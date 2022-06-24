<?php

class Script
{
    private $time_pre;

    // export the variables in environment
    public function __construct($legislature = 15)
    {
        ini_set('memory_limit', '2048M');
        $this->time_pre = microtime(true);;
    }


    function __destruct()
    {
        $time_post = microtime(true);
        $exec_time = $time_post - $this->time_pre;
        echo ("Script is over ! It took: " . round($exec_time, 2) . " seconds.\n");
    }

    public function resmush(){
      echo "resmush starting \n";
      $source_dir = __DIR__ . "/../assets/imgs/deputes_ogp/original/";
      $output_dir = __DIR__ . "/../assets/imgs/deputes_ogp/";
      $files = scandir($source_dir);
      unset($files[0]);
      unset($files[1]);

      foreach ($files as $file) {
        echo $file . "\n";

        $new_file = $output_dir . "" . $file;

        if (!file_exists($new_file)) {
          echo "Resmush starting \n";

          $input = $source_dir . "" . $file;

          $mime = mime_content_type($input);
          $info = pathinfo($input);
          $name = $info['basename'];
          $output = new CURLFile($input, $mime, $name);
          $data = array(
              "files" => $output,
          );

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/');
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          $result = curl_exec($ch);

          if (curl_errno($ch)) {
            $result = curl_error($ch);
          }
          curl_close($ch);

          $arr_result = json_decode($result);

          if ($arr_result->dest) {
            file_put_contents($new_file, file_get_contents($arr_result->dest));
            $reducedBy = ($arr_result->src_size - $arr_result->dest_size) / $arr_result->src_size * 100;
            echo "file size reduced by " . $reducedBy . "% = (src_size-dest_size)/src_size \n";
          }

        }

        echo "\n";
      }

    }

}
$script = new Script();
$script->resmush();
