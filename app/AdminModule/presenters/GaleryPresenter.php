<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;
use Nette\Utils\Finder;
use Nette\Utils\Image;
use Nette\Database\SqlLiteral;

final class GaleryPresenter extends WebPresenter {        
    
    private $galery_dir = "galery";
    
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
    public function beforeRender() {
        $this->template->wide_layout = true;
		$this->template->addFilter('dynamicDate', function ($date) {
			if(strlen($date) == 4)
				return $date;
			elseif(strlen($date) == 7)
				return date("m.Y", strtotime($date));
			elseif (strlen($date) == 10)
				return date("d.m.Y", strtotime($date));
		});		
		
		$this->template->galery_dir = $this->galery_dir;
    }
    
	function renderGalery() {	
		$this->template->images_dir_count = Finder::findFiles('*.jpg', '*.jpeg', '*.gif', '*.png')->in($this->galery_dir."/photos")->count();
		
		$this->template->photos = $this->photo
									   ->findAll()
									   ->order("order_sort ASC");
	}

	function handleUploadFile() {		
		// Set the allowed file extensions
		$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions
   		
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);
		
		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			$tempFile   = $_FILES['Filedata']['tmp_name'];
			$uploadDir  = $this->galery_dir."/photos";
			$targetFile = Str_Replace(Array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž"),
 			       					  Array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z"),
                                      $_FILES['Filedata']['name']
                                     );
		
			// Validate the filetype
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				// Save the file
				
				$image = Image::fromFile($tempFile);

				$image->resize(NULL, 1200, Image::SHRINK_ONLY);
				$image->save($this->galery_dir."/photos/{$targetFile}");
				chmod($this->galery_dir."/photos/{$targetFile}", 0777);
                $filesize = filesize($this->galery_dir."/photos/{$targetFile}");
                
                $new_width = $image->width;
                $new_height = $image->height;
                
                $image->resize(NULL, 240);
				$image->save($this->galery_dir."/thumbs/{$targetFile}");
				chmod($this->galery_dir."/thumbs/{$targetFile}", 0777);

                unset($image);
                								
				$photo = $this->photo->findBy(array("file" => $targetFile));
				$max_position = $this->photo->findAll()
                                            ->max('order_sort');
				
				if($photo->count() == 0) { // fotka s tímto názvem souboru neexistuje, vloží se nakonec				
    				$new_photo = $this->photo->insert(["file" => $targetFile,
    						 						   "width" => $new_width,
    												   "height" => $new_height,
    												   "order_sort" => $max_position + 1,
                                                     ]);

                    Debugger::enable(Debugger::PRODUCTION); // háček kvůli tomu, aby se v ajaxové odpovědi neodesílal debug bar
                    $this->setView("photo-box");
                    $this->template->photo = $this->photo->get($new_photo->id);
				}
				
				else { // fotka s tímto názvem souboru existuje, aktualizuje se    				
    				$photo->update(['width' => $new_width,
    				                'height' => $new_height,
                                  ]);
    				                                          
                    //$this->handleUpdatePosition($new_photo->id, $max_position);
                    $photo = $photo->fetch();
                    $this->payload->photo = $this->photo->get($photo->id)
                                                        ->toArray();
                    
                    $filesize = \Latte\Runtime\Filters::bytes(filesize($this->galery_dir."/photos/".$targetFile));
                    $this->payload->filesize = $filesize;
                    $this->payload->file_path = $this->galery_dir."/thumbs/{$targetFile}";
                    
            		$this->payload->images_dir_count = Finder::findFiles('*.jpg', '*.jpeg', '*.gif', '*.png')->in($this->galery_dir."/photos")
            		                                                                                         ->count();
            		$this->payload->images_db_count = $this->photo->findAll()
            		                                              ->count();
                    $this->sendPayload();
                    
				}
			}
		}
	}

	function handleRemovePhoto($photo_id) {
		$photo = $this->photo->get($photo_id);
		
		@unlink($this->galery_dir."/photos/{$photo->file}");
		@unlink($this->galery_dir."/thumbs/{$photo->file}");

		$this->photo->findAll()
		            ->where('order_sort > ?', $photo->order_sort)
                    ->update(["order_sort" => new SqlLiteral("order_sort - 1")]);

		$this->photo->delete($photo_id);
		$this->payload->images_dir_count = Finder::findFiles('*.jpg', '*.jpeg', '*.gif', '*.png')->in($this->galery_dir."/photos")
		                                                                                         ->count();
		$this->payload->images_db_count = $this->photo->findAll()
		                                              ->count();
		$this->payload->success = true;
		$this->sendPayload();
		$this->terminate();
	}

	function actionGeneratePhotos($galery_id) {
		$this->photo->findAll()
		            ->delete();

		foreach (Finder::findFiles('*.jpg', '*.jpeg', '*.gif', '*.png')->in($this->galery_dir."/thumbs") as $file_path => $file) {
			unlink($file_path);
		}
		
		foreach (Finder::findFiles('*.jpg', '*.jpeg', '*.gif', '*.png')->in($this->galery_dir."/photos") as $file_path => $file) {			
			$image = Image::fromFile($file_path);

			$image->resize(NULL, 1200);
			$image->save($this->galery_dir."/photos/".basename($file_path));
			chmod($this->galery_dir."/photos/".basename($file_path), 0777);

			$max_position = $this->photo->findAll()
                                        ->max('order_sort');

			$this->photo->insert(array("file" => basename($file_path),
									   "width" => $image->width,
									   "height" => $image->height,
									   "order_sort" => $max_position + 1,
									  ));

			$image->resize(NULL, 240);
			$image->save($this->galery_dir."/thumbs/".basename($file_path));
			
			unset($image);
		}
		
		$this->redirect("galery");
	}
	
	
	function handleUpdateDescription($photo_id, $text) {
    	$this->photo->update(["id" => $photo_id], ["description" => $text]);
    	$this->sendPayload();
	}
	
	function handleUpdatePosition($photo_id, $new_position) {
    	$this->setView("galery");
        $old_position = $this->photo->get($photo_id)
                                    ->order_sort;
                             
        if($old_position != $new_position) {
            $max_position = $this->photo->findAll()
                                        ->max('order_sort');
            
            $this->photo->update(['id' => $photo_id], ['order_sort' => $new_position]);
            $sign = $old_position < $new_position ? "-" : "+";
            $this->photo->findAll()
                        ->where("id != ? AND order_sort BETWEEN ? AND ?", $photo_id, min($old_position, $new_position), max($old_position, $new_position))
                        ->update(["order_sort" => new SqlLiteral("order_sort {$sign} 1")]);
        }
        
        $this->sendPayload();
	}
}
