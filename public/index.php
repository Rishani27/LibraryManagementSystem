<?php
/**
 * ULMS - Root Index
 * This file exists primarily for shared hosting environments (like InfinityFree) 
 * that require an index.php in the root directory to prevent a 403 Forbidden error.
 * It simply redirects all root traffic to the public/ directory.
 */
header("Location: public/index.php");
exit();
