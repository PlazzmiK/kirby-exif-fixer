<?php
Kirby::plugin('PlazzmiK/kirby-exif-fixer', [
    'options' => [
        'maxWidth' => 2000
    ],
    'hooks' => [
		'file.create:after' => function ($file) {
			if(array_key_exists("Orientation", $file->exif()->data())) {
				if ($file->exif()->data()["Orientation"] == 6) {
					$img = new Imagick($file->root());
					$imageprops = $img->getImageGeometry();
					$width = $imageprops['width'];
					$height = $imageprops['height'];

					$img->rotateimage("#000", 90);
					$img->stripImage();
					$img->resizeImage($height,$width, imagick::FILTER_LANCZOS, 0.9, true);
					$img->writeImage($file->root());
				}
			}
			if($file->isResizable()) {
				if($file->width() > option('PlazzmiK.kirby-exif-fixer.maxWidth')) {
					try {
						kirby()->thumb($file->root(), $file->root(), [
							'width' => option('PlazzmiK.kirby-exif-fixer.maxWidth'),
							'autoOrient' => true
						]);
					} catch (Exception $e) {
						throw new Exception($e->getMessage());
					}
				}
			}
		},
		'file.replace:after' => function ($newFile, $oldFile) {
			kirby()->trigger('file.create:after', $newFile);
        }
    ]
]);
?>
