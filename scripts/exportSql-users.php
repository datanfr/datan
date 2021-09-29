<?php

define("MAX_EXECUTION_TIME", -1); // seconds
ini_set('memory_limit', '-1');

$timeline = time() + MAX_EXECUTION_TIME;

EXPORT_TABLES($_SERVER['DATABASE_HOST'], $_SERVER['DATABASE_USERNAME'], $_SERVER['DATABASE_PASSWORD'], $_SERVER['DATABASE_NAME'], array("users"));

function EXPORT_TABLES($host, $user, $pass, $name, $tables = false, $backup_name = false)
{
    $tablesData = array(
      'users'
    );
    $mysqli = new mysqli($host, $user, $pass, $name);
    $mysqli->select_db($name);
    $mysqli->query("SET NAMES 'utf8'");
    $queryTables = $mysqli->query('SHOW TABLES');
    while ($row = $queryTables->fetch_row())
    {
        $target_tables[] = $row[0];
    }
    if ($tables !== false)
    {
        $target_tables = array_intersect($target_tables, $tables);
    }
    try
    {
        foreach ($target_tables as $table)
        {
          if (in_array($table, $tablesData)) {
            $result = $mysqli->query('SELECT * FROM ' . $table);
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content = (!isset($content) ? '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";
            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i ++, $st_counter = 0)
            {
                while ($row = $result->fetch_row())
                { //when started (and every after 100 command cycle):
                    if ($st_counter % 100 == 0 || $st_counter == 0)
                    {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; $j ++)
                    {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j]))
                        {
                            $content .= '"' . $row[$j] . '"';
                        } else
                        {
                            $content .= '""';
                        }
                        if ($j < ($fields_amount - 1))
                        {
                            $content .= ',';
                        }
                    }
                    $content .= ")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num)
                    {
                        $content .= ";";
                    } else
                    {
                        $content .= ",";
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
          } else {
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content = (!isset($content) ? '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";
            $content .= "\n\n\n";
          }
        }
    } catch (Exception $e)
    {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }
    $backup_name = $backup_name ? $backup_name : $name . "_votes_datan_backup_" . date('Y-m-d') . ".sql";
    //header('Content-Type: application/octet-stream');
    //header("Content-Transfer-Encoding: Binary");
    //header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
    //echo $content;

    // NEW SYSTEM ==> Save the file in the update_dataset/backup folder

    $date = date("Y-m-d");
    $file = 'update_dataset/backup/users/' . date("Ymd") .'.sql';

    if(!is_file($file)){
      file_put_contents($file, "");

      $contentFinal = " /* DATABASE BACKUP FOR THE users TABLE \n";
      $contentFinal .= " Date of the backup: " . $date . " */ \n";
      $contentFinal .= $content;

      file_put_contents($file, $contentFinal);
    } else {
      die("File alreay exists!");
    }

    exit;
}

?>
