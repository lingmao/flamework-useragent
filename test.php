<?
	#
	# to get common referers from an access log:
	# cat log | awk -F'(" "| "|" )' '{print $5}' | sort | uniq -c | sort -nr
	#
	#
	# to get a list of the top 100 UAs:
	# cat log | awk -F'(" "| "|" )' '{print $5}' | sort | uniq -c | sort -nr | head -n 100 | awk '{$1=""; print substr($0,2)}'
	#


	header('Content-type: text/plain');
	include('lib_useragent.php');


	$test_file = 'top-2011-11-19';

	$i = 1;
	$ua = null;


	#
	# file reader
	#

	$fh = fopen($test_file, 'r');
	if (!$fh) exit;
	while (($line = fgets($fh, 4096)) !== false){
		process_line($line);
	}
	if (!feof($fh)){
		echo "Error: unexpected fgets() fail\n";
		exit;
	}
	fclose($fh);



	function process_line($line){

		global $ua;

		if (preg_match('!^\t!', $line)){

			$line = trim($line);

			if (is_null($ua)){
				echo "found match line with no UA: $line\n";
				return;
			}

			$parts = preg_split('!\s+!', $line);

			run_test($ua, $parts);

			$ua = null;
		}else{
			$line = trim($line);
			if (strlen($line)){
				$ua = $line;
			}else{
				$ua = null;
			}
		}
	}

	function run_test($ua, $parts){

		global $i;

		#echo "target: $ua\n";
		#echo "match: ";
		#print_r($parts);

		$map = array(
			0 => array('agent', 'agent_version'),
			1 => array('engine', 'engine_version'),
			2 => array('os', 'os_version'),
		);


		$ret = useragent_decode($ua);
		$pass = 0;

		do {

			foreach ($map as $k => $fields){

				if ($parts[$k]){
					list($a, $b) = explode('/', $parts[$k]);

					if ($ret[$fields[0]] != $a){
						echo "$i not ok\n";
						echo "# $ua\n";
						echo "# expecting $fields[0] $a, got {$ret[$fields[0]]}\n";
						break 2;
					}
					if ($ret[$fields[1]] != $b){
						echo "$i not ok\n";
						echo "# $ua\n";
						echo "# expecting $fields[1] $b, got {$ret[$fields[1]]}\n";
						break 2;
					}
				}
			}

			echo "$i ok\n";
			#print_r($ret);

		} while (0);

		$i++;
	}
