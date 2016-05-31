<?php
namespace Osquatro\cards;

class TableExporter
{
    const PNG_EXTENSION = 'png';
    const GIF_EXTENSION = 'gif';

    const PATTERN_RESOURCE_PATH = 'Misc/table-pattern.png';
    const TABLE_WIDTH = 600;
    const TABLE_HEIGHT = 500;

    const CARD_WIDTH = 71;
    const CARD_HEIGHT = 96;


    var $exportPath;
    var $imagePath;

    function __construct($imagePath, $exportPath) {
        $this->exportPath = $exportPath;
        $this->imagePath = $imagePath;
    }

    public function exportPNG($table) {
        $im = $this->createTable($table);
        $this->putCardsOnTable($im, $table->{'cards'});
        if ($table->{'frame'}) {
           $im = $this->processFrame($im, $table->{'frameParams'});
        }
        return $this->export($im);
    }

    private function createTable($table) {
        $im = @imagecreatetruecolor(self::TABLE_WIDTH, self::TABLE_HEIGHT)
            or die("Cannot Initialize new GD image stream");

        $background = imagecolorallocatealpha( $im, 0, 153, 76, 0 );
        $this->createTiles($im);
        imagefill( $im, 0, 0, $background );

        return $im;
    }

    private function createTiles($im) {
        $pattern = imagecreatefrompng(sprintf("%s/%s", $this->imagePath, self::PATTERN_RESOURCE_PATH));
        imagealphablending($pattern, true);
        imagesettile($im, $pattern);
        // Make the image repeat
        imagefilledrectangle($im, 0, 0, self::TABLE_WIDTH, self::TABLE_HEIGHT, IMG_COLOR_TILED);
    }

    private function putCardsOnTable($im, $cards) {
        $images = array();
        foreach ($cards as $card) {
            $key = preg_replace("/-/", "/", $card->{'key'});

            $image = @imagecreatefrompng(sprintf("%s/%s", $this->imagePath, $key));
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $images[] = $image;

            imagecopyresampled ($im, $image,
                $card->{'x'} - self::CARD_WIDTH / 2,
                $card->{'y'} - self::CARD_HEIGHT / 2,
                0,
                0,
                self::CARD_WIDTH,
                self::CARD_HEIGHT,
                self::CARD_WIDTH,
                self::CARD_HEIGHT);
        }
        foreach ($images as $img) {
            imagedestroy($img);
        }
    }

    private function processFrame($im, $frameParams) {
        $newim = @imagecreatetruecolor($frameParams->{'width'}, $frameParams->{'height'})
            or die("Cannot Initialize new GD image stream");

        imagecopy ($newim, $im, 0, 0,
            $frameParams->{'x'} - $frameParams->{'width'} / 2,
            $frameParams->{'y'} - $frameParams->{'height'} / 2,
            $frameParams->{'width'},
            $frameParams->{'height'});

        imagedestroy($im); // clean old image and return new one

        return $newim;
    }

    private function export($im) {
        $fileName = sprintf("%s.%s",
            md5(time()+date("Z")),
            self::PNG_EXTENSION );

        imagepng($im, sprintf("%s/%s", $this->exportPath, $fileName));
        imagedestroy($im);

        return $fileName;
    }

    public function exportGIF(array $tables, $delay=100) {
        $frames  = array();
        $framed = array();

        foreach ($tables as $table) {
            $im = $this->createTable($table);
            $this->putCardsOnTable($im, $table->{'cards'});
            if ($table->{'frame'}) {
               $im = $this->processFrame($im, $table->{'frameParams'});
            }
            ob_start();
            imagegif($im);
            $frames[]=ob_get_contents();
            $framed[]=$delay;
            ob_end_clean();
            imagedestroy($im);
        }

        $gif = new \GIFEncoder($frames, $framed, 0, 2, 0 , 0, 0, 'bin');
        $animation = $gif->GetAnimation();

        $fileName = sprintf("%s.%s", md5(time()+date("Z")), self::GIF_EXTENSION );

        $fp = fopen(sprintf("%s/%s", $this->exportPath, $fileName), 'w');
        fwrite($fp, $animation);
        fclose($fp);

        return $fileName;
    }
}