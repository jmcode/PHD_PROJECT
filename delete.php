<?php 
// Example: delete_directory('some_folder');
function delete_directory($dir) {
	if(strstr($dir, '../')) return false; // nothing like a little bit of extra security at least.
	system('rm -rf ' . escapeshellarg($dir), $retval);
	return $retval == 0; // UNIX commands return zero on success
}

delete_directory('DELETE');