<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

use Symfony\Component\Process\Process;
use IMDB\FileOperations\FileOperations;
use IMDB\Model\Show;

require 'vendor/autoload.php';

header('Content-Type: application/json');


$s_file_path = 'data/movies.list';
$a_response = array('method' => $s_method = $_SERVER['REQUEST_METHOD']);

$a_sortMap = array(
	'year' => 3
);
// input sanitization=
$i_limit = isset($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 100;
$i_offset = isset($_GET['offset']) && $_GET['offset'] > 0 ? (int)$_GET['offset'] : 0;
$i_sort_column = isset($_GET['sort']) && isset($a_sortMap[$_GET['sort']]) ? $a_sortMap[$_GET['sort']] : null;
// since we are not querying a database, no need to escape.
$s_FilterTitle = isset($_GET['filterTitle']) ? $_GET['filterTitle'] : null;

$i_lineNumber = isset($_GET['lineNumber']) ? (int)$_GET['lineNumber'] : null;

try {
	// I prefer to use Unix cmd line, since file sanitization and sorting is way easier and faster.
	$o_file = FileOperations::create('unix', 'data/movies.list');

	switch ($s_method):
		case 'GET':
			if (isset($_GET['initialize']) && $_GET['initialize'] == 1) {
				if (!$o_file->saveFormatted(
					'ftp://ftp.fu-berlin.de/pub/misc/movies/database/movies.list.gz',
					'data/movies.list'
				)
				) {
					$a_response['status'] = 0;
					$a_response['error'] = 'Database could not be fetched.';
				} else {
					$a_response['status'] = 200;
				}

				break;
			}

			$a_response['status'] = 200;
			$a_response['numberOfAllResults'] = $o_file->getSize();
			$a_response['numberOfReturnedResults'] = 0;

			// source might be different than actual file path Since we are going to keep sorted versions etc in another path.
			$s_source_file_path = $s_file_path;

			if ($i_sort_column) {
				$s_sorted_file_path = $o_file->sort($i_sort_column);
				$o_file = FileOperations::create('unix', $s_sorted_file_path);
			}

			if ($s_FilterTitle) {
				$a_shows = $o_file->getByKeyword($s_FilterTitle, $i_offset, $i_limit);
			} else {
				$a_shows = $o_file->get($i_offset, $i_limit);
			}

			if (count($a_shows) > 1) {
				$a_response['shows'] = array();
				foreach ($a_shows as $i_line => $s_show) {
					if (!$s_show) {
						continue;
					}
					$o_show = new Show($o_file);
					$o_show->loadFromLine($s_show);
					array_push($a_response['shows'], $o_show->toArray());
				}
				$a_response['numberOfReturnedResults'] = count($a_response['shows']);
			}
			$a_response['limit'] = $i_limit;
			$a_response['offset'] = $i_offset;
			break;
		case 'DELETE':
			if(!$i_lineNumber > 0) {
				throw new Exception('Invalid request.');
			}
			$o_show = new Show($o_file, $i_lineNumber);
			$o_show->delete();
			$a_response['status'] = 200;
			break;
		case 'PATCH':
			break;
	endswitch;

} catch (Exception $o_exception) {
	$a_response['error'] = $o_exception->getMessage();
}

echo json_encode($a_response);
