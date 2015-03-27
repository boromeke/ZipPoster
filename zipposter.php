<?php
/*
Plugin Name: ZipPoster
Donations: <a href="https://www.paypal.com/id/cgi-bin/webscr?cmd=_flow&SESSION=5LfBhUqtOZyA2Yl9nhcHTa2e2xrInwrNRBIRhTLG-8c7WBbjvKRpXWbv8Gy&dispatch=5885d80a13c0db1f8e263663d3faee8d4b3d02051cb40a5393d96fec50118c72"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"></a>

Plugin URI: http://www.richmondneuter.org/
Description:  Lets You Post 100's of Articles at Once, With Future Date Timestamps So Your Site Continues Growing Automatically For Months... Or Even Years To Come!
Author: Yudhio
Version: 1.0.0
Author URI: http://www.richmondneuter.org/
*/ 


if ( $_GET ) {
	if ( $_GET['DEBUG'] == 'true' ){
		$GLOBALS['kbm_ADPS2_DEBUG'] = true;
	}
}else{
	$GLOBALS['kbm_ADPS2_DEBUG'] = false;
}

add_action ('admin_menu','KBM_ZipPoster_add_pages');
register_activation_hook ( 'ZipPoster/ZipPoster.php' , 'KBM_ZipPoster_activation' );
register_deactivation_hook ( 'ZipPoster/ZipPoster.php' , 'KBM_ZipPoster_deactivation' );

function KBM_ZipPoster_activation() {
	$kbm_ZipPoster_Configuration = array(
		'MinPostTimeNumber'=>12,
		'MinPostTimeType'=>'Hours',
		'MaxPostTimeNumber'=>36,
		'MaxPostTimeType'=>'Hours',
		'CatagoryID'=>0,
		'UpdateNotification'=>true,
	);

	$kbm_ZipPoster_TempOption = get_option('kbm_ZipPoster_Configuration');
	if ($kbm_ADPS2_TempOption=='') {
		update_option('kbm_ZipPoster_Configuration', maybe_serialize($kbm_ZipPoster_Configuration) );
	}
}
function KBM_ZipPoster_deactivation() {
	delete_option('kbm_ZipPoster_Configuration');
}
function KBM_ZipPoster_add_pages() {
                      // Window Title       Options Menu  Permisisons URL            function to display page
	add_options_page('ZipPoster Options','ZipPoster','manage_options','ZipPosterOptions','KBM_ZipPoster');
}

function KBM_ZipPoster() {
	KBM_ZipPoster_Debug ('KBM_ZipPoster - STARTED');
	// Load Options
	$kbm_ZipPost_Configuration = KBM_ZipPoster_load_options () ;
	KBM_ZipPoster_Debug ('	options loaded');
	KBM_ZipPoster_Debug ($kbm_ZipPost_Configuration);
	
	// Load Form
	$kbm_ZipPost_Configuration = KBM_ZipPoster_load_form ($kbm_ZipPost_Configuration) ;
	KBM_ZipPoster_Debug ('	form loaded');
	KBM_ZipPoster_Debug ($kbm_ZipPost_Configuration);
	
	// Error Check Options

	// Update Options
	KBM_ZipPoster_update_options ( $kbm_ZipPost_Configuration );
	KBM_ZipPoster_Debug ('	options updated');
		
	// Process plugin function
	KBM_ZipPoster_Process ( $kbm_ZipPost_Configuration );
	KBM_ZipPoster_Debug ('	posting finished');

	// Check on the auto update notification
	KBM_ZipPoster_handle_update_notification ( $kbm_ZipPost_Configuration );
	
	// Display Options Page
	KBM_ZipPoster_display_options_page ( $kbm_ZipPost_Configuration ) ;
	KBM_ZipPoster_Debug ('	display finished');
	
	KBM_ZipPoster_Debug ('KBM_ZipPoster - FINISHED');
}

function KBM_ZipPoster_Debug ( $kbm_ZipPoster_DebugMessage ) {
	if ( $GLOBALS['kbm_ADPS2_DEBUG'] == true ) {
		if  ( is_string ( $kbm_ZipPoster_DebugMessage ) ) {
			KBM_ZipPoster_DisplayMessages ( $kbm_ZipPoster_DebugMessage , 0 ) ;
		} else {
			echo ('<!---'."\n");
			KBM_ZipPoster_DisplayMessages ( var_dump ( $kbm_ZipPoster_DebugMessage ) , 0 ) ;
			echo ('--->'."\n");
		}
	}
}

function KBM_ZipPoster_DisplayMessages ( $kbm_ZipPoster_Message, $kbm_ZipPoster_Status ) {
	static $KBM_ZipPoster_MessageArray = array();
	
	if ( $kbm_ZipPoster_Status == 0 ) {
		echo '<!-- '.$kbm_ZipPoster_Message.' -->'."\n";
	}else if ( $kbm_ZipPoster_Message == 'DISPLAY MESSAGES NOW' ) {
		if ( $kbm_ZipPoster_Status == 255 ) {
			foreach ( $KBM_ZipPoster_MessageArray as $kbm_ZipPoster_DebugMessage){
				if ( $kbm_ZipPoster_DebugMessage[0] == 1 ) {
					echo '<div id="message" class="updated fade"><p><strong>Notice: '.$kbm_ZipPoster_DebugMessage[1].'</strong></p></div>'."\n";
				}else if ( $kbm_ZipPoster_DebugMessage[0] == -1 ) {
					echo '<div class="error"><ul><li><strong>Error</strong>: '.$kbm_ZipPoster_DebugMessage[1].'</li></ul></div>'."\n";
				}else if ( $kbm_ZipPoster_DebugMessage[0] == 2 ) {
					// TODO: Change URL in this echo statement so that it has a valid URL for the update page
					echo '<div id="message" class="updated" style="border-color:rgb(0,0,255);background-color:rgb(192,192,255)"><p><strong>Update: version '.$kbm_ZipPoster_DebugMessage[1].' of ZipPoster is now Available at:<br><a href url="http://www.gurugazette.com/ZipPoster/v1xUpdaTe.php">http://www.gurugazette.com/ZipPoster/v1xUpdaTe.php</a></strong></p></div>'."\n";
				}
			}
		}
	}else{
		$KBM_ZipPoster_MessageArray[] = array( 0 => $kbm_ZipPoster_Status, 1 => $kbm_ZipPoster_Message );
	}	
}

function KBM_ZipPoster_load_options () {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_load_options - STARTED');
	if ( maybe_unserialize ( get_option('kbm_ZipPoster_Configuration') ) ){
		KBM_ZipPoster_Debug ('unserialize configuration option');
		$kbm_ZipPoster_Configuration = maybe_unserialize ( get_option('kbm_ZipPoster_Configuration') );
		KBM_ZipPoster_Debug ('configuration is: ');
		KBM_ZipPoster_Debug ($kbm_ZipPoster_Configuration);
	}else{
		KBM_ZipPoster_Debug ('using default configuration.');
		KBM_ZipPoster_Debug ('This should only happen when the plugin is first installed.');
		$kbm_ZipPoster_Configuration = array(
			'MinPostTimeNumber'=>18,
			'MinPostTimeType'=>'Hours',
			'MaxPostTimeNumber'=>30,
			'MaxPostTimeType'=>'Hours',
			'CatagoryID'=>0,
			'UpdateNotification'=>true,
		);
		KBM_ZipPoster_Debug ('configuration is: ');
		KBM_ZipPoster_Debug ($kbm_ZipPoster_Configuration);
	}
	KBM_ZipPoster_Debug ('KBM_ZipPoster_load_options - FINISHED');
	return ( $kbm_ZipPoster_Configuration );
}

function KBM_ZipPoster_load_form ( $kbm_ZipPost_Configuration ) {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_load_form - STARTED');
	
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_EarlyNumberInput');
	if ($_REQUEST['kbm_ZipPoster_EarlyNumberInput'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_EarlyNumberInput = '.$_REQUEST['kbm_ZipPoster_EarlyNumberInput']);
		$kbm_ZipPost_Configuration[MinPostTimeNumber] = $_REQUEST['kbm_ZipPoster_EarlyNumberInput'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_EarlyTypeInput');
	if ($_REQUEST['kbm_ZipPoster_EarlyTypeInput'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_EarlyTypeInput = '.$_REQUEST['kbm_ZipPoster_EarlyTypeInput']);
		$kbm_ZipPost_Configuration[MinPostTimeType] = $_REQUEST['kbm_ZipPoster_EarlyTypeInput'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_LateNumberInput');
	if ($_REQUEST['kbm_ZipPoster_LateNumberInput'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_LateNumberInput = '.$_REQUEST['kbm_ZipPoster_LateNumberInput']);
		$kbm_ZipPost_Configuration[MaxPostTimeNumber] = $_REQUEST['kbm_ZipPoster_LateNumberInput'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_LateTypeInput');
	if ($_REQUEST['kbm_ZipPoster_LateTypeInput'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_LateTypeInput = '.$_REQUEST['kbm_ZipPoster_LateTypeInput']);
		$kbm_ZipPost_Configuration[MaxPostTimeType] = $_REQUEST['kbm_ZipPoster_LateTypeInput'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_Category_Select');
	if ($_REQUEST['kbm_ZipPoster_Category_Select'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_Category_Select = '.$_REQUEST['kbm_ZipPoster_Category_Select']);
		$kbm_ZipPost_Configuration[CatagoryID] = $_REQUEST['kbm_ZipPoster_Category_Select'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_Start_Time_Input');
	if ($_REQUEST['kbm_ZipPoster_Start_Time_Input'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_Start_Time_Input = '.$_REQUEST['kbm_ZipPoster_Start_Time_Input']);
		$kbm_ZipPost_Configuration[PostStartTime] = $_REQUEST['kbm_ZipPoster_Start_Time_Input'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_PostTitle');
	if ($_REQUEST['kbm_ZipPoster_PostTitle'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_PostTitle = '.$_REQUEST['kbm_ZipPoster_PostTitle']);
		$kbm_ZipPost_Configuration[PostTitle] = $_REQUEST['kbm_ZipPoster_PostTitle'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_PostEntry');
	if ($_REQUEST['kbm_ZipPoster_PostEntry'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_PostEntry = '.$_REQUEST['kbm_ZipPoster_PostEntry']);
		$kbm_ZipPost_Configuration[PostEntry] = $_REQUEST['kbm_ZipPoster_PostEntry'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_File');
	if ($_REQUEST['kbm_ZipPoster_File'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_File = '.$_REQUEST['kbm_ZipPoster_File']);
		$kbm_ZipPost_Configuration[PostFile] = $_REQUEST['kbm_ZipPoster_File'];
	}
	KBM_ZipPoster_Debug ('Checking form for: kbm_ZipPoster_UpdateNotification');
	if ($_REQUEST['kbm_ZipPoster_UpdateNotification'] != ''){
		KBM_ZipPoster_Debug ('kbm_ZipPoster_UpdateNotification = '.$_REQUEST['kbm_ZipPoster_UpdateNotification']);
		$kbm_ZipPost_Configuration[UpdateNotification] = $_REQUEST['kbm_ZipPoster_UpdateNotification'];
	}else{
		$kbm_ZipPost_Configuration[UpdateNotification] = 'true';
	}
	
	KBM_ZipPoster_Debug ('KBM_ZipPoster_load_form - FINISHED');
	return ( $kbm_ZipPost_Configuration );
}

function KBM_ZipPoster_update_options ( $kbm_ZipPost_Configuration ) {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_update_options - STARTED');
	$kbm_ZipPoster_Configuration_New = array(
		'MinPostTimeNumber'=>$kbm_ZipPost_Configuration['MinPostTimeNumber'],
		'MinPostTimeType'=>$kbm_ZipPost_Configuration['MinPostTimeType'],
		'MaxPostTimeNumber'=>$kbm_ZipPost_Configuration['MaxPostTimeNumber'],
		'MaxPostTimeType'=>$kbm_ZipPost_Configuration['MaxPostTimeType'],
		'CatagoryID'=>$kbm_ZipPost_Configuration['CatagoryID'],
		'UpdateNotification'=>$kbm_ZipPost_Configuration['UpdateNotification'],
	);
	KBM_ZipPoster_Debug ('CURRENT configuration is: ');
	KBM_ZipPoster_Debug ($kbm_ZipPoster_Configuration_New);

	update_option('kbm_ZipPoster_Configuration', maybe_serialize($kbm_ZipPoster_Configuration_New) );
	KBM_ZipPoster_Debug ('configuration stored as a WP option');
	
	KBM_ZipPoster_Debug ('KBM_ZipPoster_update_options - FINISHED');
}

function KBM_ZipPoster_Process ( $kbm_ZipPost_Configuration ) {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_Process - STARTED');
//	echo ( $kbm_ZipPost_Configuration[PostFile] ) ;
//	echo ( $_FILES['kbm_ZipPoster_File']['tmp_name'] ) ;
	
	$kbm_ZipPoster_PostCount = 0;
	KBM_ZipPoster_Debug ('kbm_ZipPoster_PostCount set to 0');

/*
			echo ZIPARCHIVE::ER_MULTIDISK."\n";
			echo ZIPARCHIVE::ER_RENAME."\n";
			echo ZIPARCHIVE::ER_CLOSE."\n";
			echo ZIPARCHIVE::ER_SEEK."\n";
			echo ZIPARCHIVE::ER_READ."\n";
			echo ZIPARCHIVE::ER_WRITE."\n";
			echo ZIPARCHIVE::ER_CRC."\n";
			echo ZIPARCHIVE::ER_ZIPCLOSED."\n";
			echo ZIPARCHIVE::ER_NOENT."\n";
			echo ZIPARCHIVE::ER_EXISTS."\n";
			echo ZIPARCHIVE::ER_OPEN."\n";
			echo ZIPARCHIVE::ER_TMPOPEN."\n";
			echo ZIPARCHIVE::ER_ZLIB."\n";
			echo ZIPARCHIVE::ER_MEMORY."\n";
			echo ZIPARCHIVE::ER_CHANGED."\n";
			echo ZIPARCHIVE::ER_COMPNOTSUPP."\n";
			echo ZIPARCHIVE::ER_EOF."\n";
			echo ZIPARCHIVE::ER_INVAL."\n";
			echo ZIPARCHIVE::ER_NOZIP."\n";
			echo ZIPARCHIVE::ER_INTERNAL."\n";
			echo ZIPARCHIVE::ER_INCONS."\n";
			echo ZIPARCHIVE::ER_REMOVE."\n";
			echo ZIPARCHIVE::ER_DELETED."\n";
*/
	if ( $_FILES['kbm_ZipPoster_File']['tmp_name'] != '' ) {
	
		KBM_ZipPoster_Debug ('file name found - processing file');
		KBM_ZipPoster_Debug ('$_FILES[\'kbm_ZipPoster_File\'][\'tmp_name\']: '.$_FILES['kbm_ZipPoster_File']['tmp_name']);
		$kbm_ZipPoster_Proceed = true;

		if ( extension_loaded ('zip') != true ) {
			KBM_ZipPoster_DisplayMessages ( 'the php zip extension is not available', -1 );
			$kbm_ZipPoster_Proceed = false;		
		}
		
		if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_INI_SIZE ){
			KBM_ZipPoster_DisplayMessages ( 'zip file larger than that allowed in the php.ini file', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_FORM_SIZE ){
			KBM_ZipPoster_DisplayMessages ( 'zip file larger than that allowed by the form', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_PARTIAL ){
			KBM_ZipPoster_DisplayMessages ( 'zip file only partially uploaded', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_NO_FILE ){
			KBM_ZipPoster_DisplayMessages ( 'no file was uploaded to the server', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_NO_TMP_DIR ){
			KBM_ZipPoster_DisplayMessages ( 'missing temporary folder', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_CANT_WRITE ){
			KBM_ZipPoster_DisplayMessages ( 'unable to write the file to the disk', -1 );
			$kbm_ZipPoster_Proceed = false;
		} else if ( $_FILES['kbm_ZipPoster_File']['error'] == UPLOAD_ERR_EXTENSION ){
			KBM_ZipPoster_DisplayMessages ( 'upload stopped by an extension', -1 );
			$kbm_ZipPoster_Proceed = false;
		}


		if ( $_FILES['kbm_ZipPoster_File']['size'] == 0 ){
			KBM_ZipPoster_DisplayMessages ( 'file size: 0; perhaps wrong file name', -1 );
			$kbm_ZipPoster_Proceed = false;
		}
		if ( is_uploaded_file ( $_FILES['kbm_ZipPoster_File']['tmp_name'] ) == false ){
			KBM_ZipPoster_DisplayMessages ( 'impossible temporary file', -1 );
			$kbm_ZipPoster_Proceed = false;
		}
		
		if ( $kbm_ZipPoster_Proceed == true ) {
			
			$kbm_ZipPost_ZipFile = zip_open ( $_FILES['kbm_ZipPoster_File']['tmp_name'] ) ;
			KBM_ZipPoster_Debug ('zip file opened');
			KBM_ZipPoster_Debug ('ZipFile is: '.$kbm_ZipPost_ZipFile);
			
			//if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_MULTIDISK ) {
			if ( $kbm_ZipPost_ZipFile == 1 ) {
				KBM_ZipPoster_DisplayMessages ( 'multidisk zip archives cannot be used', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_RENAME ) {
			}else if ( $kbm_ZipPost_ZipFile == 2 ) {
				KBM_ZipPoster_DisplayMessages ( 'renaming the temporary file failed', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_CLOSE ) {
			}else if ( $kbm_ZipPost_ZipFile == 3 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to close the zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_SEEK ) {
			}else if ( $kbm_ZipPost_ZipFile == 4 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to seek the correct place in the zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_READ ) {
			}else if ( $kbm_ZipPost_ZipFile == 5 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to read from the zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_WRITE ) {
			}else if ( $kbm_ZipPost_ZipFile == 6 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to write to the zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_CRC ) {
			}else if ( $kbm_ZipPost_ZipFile == 7 ) {
				KBM_ZipPoster_DisplayMessages ( 'the CRC does not match, bad file?', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_ZIPCLOSED ) {
			}else if ( $kbm_ZipPost_ZipFile == 8 ) {
				KBM_ZipPoster_DisplayMessages ( 'the zip file closed unexpectedly', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_NOENT ) {
			}else if ( $kbm_ZipPost_ZipFile == 9 ) {
				KBM_ZipPoster_DisplayMessages ( 'file does not exist', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_EXISTS ) {
			}else if ( $kbm_ZipPost_ZipFile == 10 ) {
				KBM_ZipPoster_DisplayMessages ( 'zip file already exists', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_OPEN ) {
			}else if ( $kbm_ZipPost_ZipFile == 11 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to open the zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_TMPOPEN ) {
			}else if ( $kbm_ZipPost_ZipFile == 12 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to create a temporary file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_ZLIB ) {
			}else if ( $kbm_ZipPost_ZipFile == 13 ) {
				KBM_ZipPoster_DisplayMessages ( 'compression library error', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_MEMORY ) {
			}else if ( $kbm_ZipPost_ZipFile == 14 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to allocate memory', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_CHANGED ) {
			}else if ( $kbm_ZipPost_ZipFile == 15 ) {
				KBM_ZipPoster_DisplayMessages ( 'entry has been unexpectedly changed', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_COMPNOTSUPP ) {
			}else if ( $kbm_ZipPost_ZipFile == 16 ) {
				KBM_ZipPoster_DisplayMessages ( 'compression method not supported', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_EOF ) {
			}else if ( $kbm_ZipPost_ZipFile == 17 ) {
				KBM_ZipPoster_DisplayMessages ( 'unexpected end of file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_INVAL ) {
			}else if ( $kbm_ZipPost_ZipFile == 18 ) {
				KBM_ZipPoster_DisplayMessages ( 'invalid argument', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_NOZIP ) {
			}else if ( $kbm_ZipPost_ZipFile == 19 ) {
				KBM_ZipPoster_DisplayMessages ( 'not a recognized zip file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_INTERNAL ) {
			}else if ( $kbm_ZipPost_ZipFile == 20 ) {
				KBM_ZipPoster_DisplayMessages ( 'internal error', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_INCONS ) {
			}else if ( $kbm_ZipPost_ZipFile == 21 ) {
				KBM_ZipPoster_DisplayMessages ( 'zip file inconsistent', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_REMOVE ) {
			}else if ( $kbm_ZipPost_ZipFile == 22 ) {
				KBM_ZipPoster_DisplayMessages ( 'unable to remove file', -1 );
				$kbm_ZipPoster_Proceed = false;
			//}else if ( $kbm_ZipPost_ZipFile == ZIPARCHIVE::ER_DELETED ) {
			}else if ( $kbm_ZipPost_ZipFile == 23 ) {
				KBM_ZipPoster_DisplayMessages ( 'zip file entry has been deleted unexpectedly', -1 );
				$kbm_ZipPoster_Proceed = false;
			}
			
			
			KBM_ZipPoster_Debug ('check that it is a good file open');
			if ( $kbm_ZipPoster_Proceed == true ) {
				KBM_ZipPoster_Debug ('attempting zip read');
				while ( $kbm_ZipPost_EntryFile = zip_read ( $kbm_ZipPost_ZipFile ) ){
					KBM_ZipPoster_Debug ('new entryfile found');
					KBM_ZipPoster_Debug ('entryfile is: '.$kbm_ZipPost_EntryFile);
					
					if ( zip_entry_filesize($kbm_ZipPost_EntryFile) > 0 ) {
						$kbm_ZipPost_Entry = zip_entry_read($kbm_ZipPost_EntryFile, zip_entry_filesize($kbm_ZipPost_EntryFile));
						KBM_ZipPoster_Debug ('new entryfile read');
						KBM_ZipPoster_Debug ('entry is: '.$kbm_ZipPost_Entry);
						
						zip_entry_close($kbm_ZipPost_EntryFile);
						KBM_ZipPoster_Debug ('entryfile has been closed');

						KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_ZipPost_Entry );
						KBM_ZipPoster_Debug ('new post created');
					
						$kbm_ZipPoster_PostCount = $kbm_ZipPoster_PostCount + 1 ;
						KBM_ZipPoster_Debug ('kbm_ZipPoster_PostCount increased to: '.$kbm_ZipPoster_PostCount);
					}else{
						KBM_ZipPoster_Debug ('file size of entry is 0; skipping');
						zip_entry_close($kbm_ZipPost_EntryFile);
						KBM_ZipPoster_Debug ('non-file entryfile has been closed');

					}
				}
			}
			zip_close($kbm_ZipPost_ZipFile);
			KBM_ZipPoster_Debug ('zipfile has been closed');
		}
		unlink ( $_FILES['kbm_ZipPoster_File']['tmp_name'] );
		KBM_ZipPoster_Debug ('attempting to remove the uploaded file');
	}else if ( $kbm_ZipPost_Configuration[PostTitle] != '' ) {
		KBM_ZipPoster_Debug ('post title found - processing post');
		$kbm_zipPost_Entry = $kbm_ZipPost_Configuration[PostTitle]."\n\n".$kbm_ZipPost_Configuration[PostEntry];
		KBM_ZipPoster_Debug ('attached post title to post:');
		KBM_ZipPoster_Debug ('kbm_zipPost_Entry: '.$kbm_zipPost_Entry);
		
		KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_zipPost_Entry ) ;
		KBM_ZipPoster_Debug ('new post created');
		$kbm_ZipPoster_PostCount = 1 ;
		KBM_ZipPoster_Debug ('kbm_ZipPoster_PostCount set to 1');
	}else{
		KBM_ZipPoster_Debug ('no articles found - not processing data');
//		echo ' Nothing Posted. ' ;
	}
	
	KBM_ZipPoster_Debug ('finished processing data');
	if ( $kbm_ZipPoster_PostCount > 0 ) {
		KBM_ZipPoster_Debug ('post count has been increased');
		KBM_ZipPoster_DisplayMessages ( $kbm_ZipPoster_PostCount.' posts created.', 1 );
		KBM_ZipPoster_Debug ('post count has been reported');
		//echo '<div id="message" class="updated fade"><p><strong>'.$kbm_ZipPoster_PostCount.' posts created.</strong></p></div>';
	}
	KBM_ZipPoster_Debug ('KBM_ZipPoster_Process - FINISHED');
}

function KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_ZipPoster_Post_Data ) {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_Create_Post - STARTED');
	//harden the cleanup of the post data
	// Change all the Paragraph Tags to 2 NewLines so that browser will display some space.
	$kbm_ZipPoster_Post_Data = str_replace ( "\n" , "KBMSAYSNEWLINE" , $kbm_ZipPoster_Post_Data ) ;
	KBM_ZipPoster_Debug ('newlines temporarily filtered');
//	$kbm_ZipPoster_Post_Data = str_replace ( "\r\n" , "\n" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\l\n" , "\n" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\n\r" , "\n" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\n\l" , "\n" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\r\l" , "\n" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\l\r" , "\n" , $kbm_ZipPoster_Post_Data ) ;
	
//	$kbm_ZipPoster_Post_Data = str_replace ( "\n\n" , "KBMSAYSNEWLINEKBMSAYSNEWLINE" , $kbm_ZipPoster_Post_Data ) ;
//	$kbm_ZipPoster_Post_Data = str_replace ( "\n" , "" , $kbm_ZipPoster_Post_Data ) ;
	
//	$kbm_ZipPoster_Post_Data = str_replace ( "http://" , " http://" , $kbm_ZipPoster_Post_Data ) ;
	
	$kbm_ZipPoster_Post_Data = preg_replace ( '/[\x0-\x1F|\x7F-\xFF]/' , '' , $kbm_ZipPoster_Post_Data ) ;
	KBM_ZipPoster_Debug ('non ascii characters removed');
	
	//Change accent charactors to unaccented characters
	$kbm_ZipPoster_Post_Data = strtr( $kbm_ZipPoster_Post_Data ,
		"\xe1\xc1\xe0\xc0\xe2\xc2\xe4\xc4\xe3\xc3\xe5\xc5" .
		"\xaa\xe7\xc7\xe9\xc9\xe8\xc8\xea\xca\xeb\xcb\xed" .
		"\xcd\xec\xcc\xee\xce\xef\xcf\xf1\xd1\xf3\xd3\xf2" .
		"\xd2\xf4\xd4\xf6\xd6\xf5\xd5\x8\xd8\xba\xf0\xfa\xda" .
		"\xf9\xd9\xfb\xdb\xfc\xdc\xfd\xdd\xff\xe6\xc6\xdf\xf8" ,
		"aAaAaAaAaAaAacCeEeEeEeEiIiIiIiInNo" .
		"OoOoOoOoOoOoouUuUuUuUyYyaAso" ) ;
	//echo ('<br>'.$kbm_ZipPoster_Post_Data.'<br>');
	KBM_ZipPoster_Debug ('non english characters converted');

	// Change all the Paragraph Tags to 2 NewLines so that browser will display some space.
	$kbm_ZipPoster_Post_Data = str_replace ( "KBMSAYSNEWLINE" , "\n" , $kbm_ZipPoster_Post_Data ) ;
	KBM_ZipPoster_Debug ('newline characters replaced');

	// Get the first non-blank line.
	// Get the first 10 words.
	// the first 10 words is THE_TITLE
	// Get the next line.	
	// the next line is THE_NEXT_LINE
	
	// the the first line is less than or equal to THE_TITLE and THE_NEXT_LINE is blank then
		// we remove THE_TITLE from the post data
	// other wise we leave the post data alone.

	// Cases:
	/* Case 1:
	  This is a really long line that is not actually stopped at line breaks.  It will just wrap around and cause funky results to appear on the page.
	  
	  Result 1:
	  Title: This is a really long line that is not actually
	  Text: This is a really long line that is not actually stopped at line breaks.  It will just wrap around and cause funky results to appear on the page.
	*/
	/* Case 2:
	  This is actually the first line in a paragraph.\nHowever it is actually stopped at line breaks. \nIt will wrap around but will not cause funky results to appear on the page.\n
	  
	  Result 1:
	  Title: This is actually the first line in a paragraph.
	  Text: This is actually the first line in a paragraph.\nHowever it is actually stopped at line breaks. \nIt will wrap around but will not cause funky results to appear on the page.\n
	*/
	/* Case 3:
	  This is a title.\n\nThis is the first line in a paragraph.\nHowever it is actually stopped at line breaks. \nIt will wrap around but will not cause funky results to appear on the page.\n
	  
	  Result 1:
	  Title: This is a title.
	  Text: This is the first line in a paragraph.\nHowever it is actually stopped at line breaks. \nIt will wrap around but will not cause funky results to appear on the page.\n
	*/
	// Code:
	$kbm_ZipPoster_TempArray = preg_split ( "/\n/", $kbm_ZipPoster_Post_Data ) ;
	KBM_ZipPoster_Debug ('split data into multile lines');
	KBM_ZipPoster_Debug ($kbm_ZipPoster_TempArray);
	// We set this to count - 1 to prevent the issue of accessing an array entry that has nothing in it.
	$kbm_ZipPoster_TempArray_Count = count ( $kbm_ZipPoster_TempArray ) - 1 ;
	KBM_ZipPoster_Debug ('count set to:');
	KBM_ZipPoster_Debug ($kbm_ZipPoster_TempArray_Count);
	$kbm_ZipPoster_Post_Title_Line = '';
	KBM_ZipPoster_Debug ('title line set to blank');
	$kbm_ZipPoster_Post_Title_Next_Line = '';
	KBM_ZipPoster_Debug ('title next line set to blank');
	for ( $kbm_ZipPoster_LineCounter = 0;  $kbm_ZipPoster_TempArray_Count >= $kbm_ZipPoster_LineCounter; $kbm_ZipPoster_LineCounter++){
		KBM_ZipPoster_Debug ('for loop at count: '.$kbm_ZipPoster_LineCounter);
		if ($kbm_ZipPoster_Post_Title_Line == ''){
			KBM_ZipPoster_Debug ('title line is still blank');
			$kbm_ZipPoster_Post_Title_Line = trim ( $kbm_ZipPoster_TempArray [ $kbm_ZipPoster_LineCounter ]  );
			KBM_ZipPoster_Debug ('title line is now: '.$kbm_ZipPoster_Post_Title_Line);
			$kbm_ZipPoster_Post_Title_Next_Line = trim ( $kbm_ZipPoster_TempArray [ $kbm_ZipPoster_LineCounter+1 ]  );
			KBM_ZipPoster_Debug ('title next line is now: '.$kbm_ZipPoster_Post_Title_Next_Line);
		}else{
			KBM_ZipPoster_Debug ('title line is not blank');
			// This will cause it to break out of the loop after it's found the first line of text
			$kbm_ZipPoster_LineCounter = $kbm_ZipPoster_TempArray_Count+1 ;
			KBM_ZipPoster_Debug ('count set to: '.$kbm_ZipPoster_LineCounter);
		}
	}
	KBM_ZipPoster_Debug ('for loop finished');
	
	$kbm_ZipPoster_WordArray = explode ( " " , $kbm_ZipPoster_Post_Title_Line ) ;
	KBM_ZipPoster_Debug ('word array set to:');
	KBM_ZipPoster_Debug ($kbm_ZipPoster_WordArray);
	if ( ( 10 > count ( $kbm_ZipPoster_WordArray ) ) AND ( $kbm_ZipPoster_Post_Title_Next_Line == '' ) ) {
		KBM_ZipPoster_Debug ('doing remove title from data steps');
		$kbm_ZipPoster_Post_Title = $kbm_ZipPoster_Post_Title_Line ;
		KBM_ZipPoster_Debug ('title set to:'.$kbm_ZipPoster_Post_Title);
		$kbm_ZipPoster_Title_Position = strpos ( $kbm_ZipPoster_Post_Data , $kbm_ZipPoster_Post_Title ) ;
		KBM_ZipPoster_Debug ('title starts at:'.$kbm_ZipPoster_Title_Position);
		$kbm_ZipPoster_Post_Data = substr_replace ( $kbm_ZipPoster_Post_Data , '' , $kbm_ZipPoster_Title_Position , strlen ( $kbm_ZipPoster_Post_Title ) ) ;
		KBM_ZipPoster_Debug ('new data is: '.$kbm_ZipPoster_Post_Data);
	} else {
		KBM_ZipPoster_Debug ('doing build new title string steps');
		$kbm_ZipPoster_TitleArray = array_chunk ( $kbm_ZipPoster_WordArray , 10 ) ;
		KBM_ZipPoster_Debug ('title array set to:');
		KBM_ZipPoster_Debug ($kbm_ZipPoster_TitleArray);
		$kbm_ZipPoster_Post_Title = implode ( ' ' , $kbm_ZipPoster_TitleArray[0] ) ;
		KBM_ZipPoster_Debug ('title set to:'.$kbm_ZipPoster_Post_Title);
	}
	
/*	
	// get the post title
	$kbm_ZipPoster_TempArray = preg_split ( "/\n/", $kbm_ZipPoster_Post_Data ) ;
	KBM_ZipPoster_Debug ('long string split into chunks');
	$kbm_ZipPoster_Post_Title = '';
	KBM_ZipPoster_Debug ('title set to blank');
	foreach ( $kbm_ZipPoster_TempArray as $kbm_ZipPoster_TempLine ){
		KBM_ZipPoster_Debug ('going line by line:');
		KBM_ZipPoster_Debug ($kbm_ZipPoster_TempLine);
		if ($kbm_ZipPoster_Post_Title == ''){
			KBM_ZipPoster_Debug ('title is still blank');
			// harden the clean up of the title line
			$kbm_ZipPoster_Post_Title = trim ( $kbm_ZipPoster_TempLine );
			KBM_ZipPoster_Debug ('title set to:');
			KBM_ZipPoster_Debug ($kbm_ZipPoster_Post_Title);
		}
	}
*/	

	// get the post date
	$kbm_ZipPost_MinSeconds = 0;
	if ( $kbm_ZipPost_Configuration['MinPostTimeType'] == 'Minutes' ){$kbm_ZipPost_MinSeconds = 60;}
	else if ( $kbm_ZipPost_Configuration['MinPostTimeType'] == 'Hours' ){$kbm_ZipPost_MinSeconds = 60*60;}
	else if ( $kbm_ZipPost_Configuration['MinPostTimeType'] == 'Days' ){$kbm_ZipPost_MinSeconds = 60*60*24;}
	else if ( $kbm_ZipPost_Configuration['MinPostTimeType'] == 'Weeks' ){$kbm_ZipPost_MinSeconds = 60*60*24*7;}
	KBM_ZipPoster_Debug ('MinSeconds set to:'.$kbm_ZipPost_MinSeconds);
	$kbm_ZipPost_MinSeconds = $kbm_ZipPost_MinSeconds * $kbm_ZipPost_Configuration['MinPostTimeNumber'];
	KBM_ZipPoster_Debug ('user MinSeconds set to:'.$kbm_ZipPost_MinSeconds);
	
	$kbm_ZipPost_MaxSeconds = 0;
	if ( $kbm_ZipPost_Configuration['MaxPostTimeType'] == 'Minutes' ){$kbm_ZipPost_MaxSeconds = 60;}
	else if ( $kbm_ZipPost_Configuration['MaxPostTimeType'] == 'Hours' ){$kbm_ZipPost_MaxSeconds = 60*60;}
	else if ( $kbm_ZipPost_Configuration['MaxPostTimeType'] == 'Days' ){$kbm_ZipPost_MaxSeconds = 60*60*24;}
	else if ( $kbm_ZipPost_Configuration['MaxPostTimeType'] == 'Weeks' ){$kbm_ZipPost_MaxSeconds = 60*60*24*7;}
	KBM_ZipPoster_Debug ('MaxSeconds set to:'.$kbm_ZipPost_MaxSeconds);
	$kbm_ZipPost_MaxSeconds = $kbm_ZipPost_MaxSeconds * $kbm_ZipPost_Configuration['MaxPostTimeNumber'];
	KBM_ZipPoster_Debug ('user MaxSeconds set to:'.$kbm_ZipPost_MaxSeconds);
	
	$kbm_ZipPost_Seconds = mt_rand($kbm_ZipPost_MinSeconds, $kbm_ZipPost_MaxSeconds);
	KBM_ZipPoster_Debug ('random time for post set to:'.$kbm_ZipPost_Seconds);
	
	static $kbm_ZipPost_Time = 0;
	KBM_ZipPoster_Debug ('post time is:'.$kbm_ZipPost_Time);
	if ( $kbm_ZipPost_Time == 0 ) {
		KBM_ZipPoster_Debug ('change time from 0 to an actual time');
		$kbm_ZipPost_Time = strtotime ( $kbm_ZipPost_Configuration[PostStartTime] ) + $kbm_ZipPost_Seconds;
		KBM_ZipPoster_Debug ('post time is:'.$kbm_ZipPost_Time);
	}else{
		$kbm_ZipPost_Time = $kbm_ZipPost_Time + $kbm_ZipPost_Seconds;
		KBM_ZipPoster_Debug ('post time is:'.$kbm_ZipPost_Time);
	}
	
	$kbm_ZipPoster_Post_Date = date("Y-m-d H:i:s", $kbm_ZipPost_Time);
	KBM_ZipPoster_Debug ('post date is:'.$kbm_ZipPoster_Post_Date);
	
	// initialize post object
	$kbm_ZipPoster_Post_Array = array ( ) ;
	KBM_ZipPoster_Debug ('post array created');
	// fill object
        $kbm_ZipPoster_Post_Array [ 'post_title' ]     = $kbm_ZipPoster_Post_Title ;
	KBM_ZipPoster_Debug ('post_title set');
	$kbm_ZipPoster_Post_Array [ 'post_content' ]   = $kbm_ZipPoster_Post_Data ;
	KBM_ZipPoster_Debug ('post_content set');
	$kbm_ZipPoster_Post_Array [ 'post_date' ]      = $kbm_ZipPoster_Post_Date ;
	KBM_ZipPoster_Debug ('post_date set');
	$kbm_ZipPoster_Post_Array [ 'post_status' ]    = 'publish' ;
	KBM_ZipPoster_Debug ('post_status set');
	$kbm_ZipPoster_Post_Array [ 'post_author' ]    = '0' ;
	KBM_ZipPoster_Debug ('post_author set');
	$kbm_ZipPoster_Post_Array [ 'comment_status' ] = 'open' ;
	KBM_ZipPoster_Debug ('comment_status set');
	$kbm_ZipPoster_Post_Array [ 'post_category' ]  = array( $kbm_ZipPost_Configuration [ 'CatagoryID' ] ) ;
	KBM_ZipPoster_Debug ('post_category set');
	
	KBM_ZipPoster_Debug ('post array:');
	KBM_ZipPoster_Debug ($kbm_ZipPoster_Post_Array);

	// ======== INSERTING =========
	wp_insert_post ( $kbm_ZipPoster_Post_Array ) ;
	KBM_ZipPoster_Debug ('post inserted into the database');

//	echo ('<h2>'.$kbm_ZipPoster_Post_Array [ 'post_title' ].'</h2>');
//	echo ($kbm_ZipPoster_Post_Array [ 'post_content' ]);
	KBM_ZipPoster_Debug ('KBM_ZipPoster_Create_Post - FINISHED');
}

function KBM_ZipPoster_handle_update_notification ( $kbm_ZipPoster_Configuration ){
	KBM_ZipPoster_Debug ('KBM_ZipPoster_handle_update_notification - STARTED');
	// if update notification option is true
	if ( $kbm_ZipPoster_Configuration['UpdateNotification'] == 'true' ) {
		KBM_ZipPoster_Debug ('update notification enabled');
		// Load remote version information
		// TODO: Update the file_get_contents so that it has a valid URL for the version number page
		$kbm_ZipPoster_remote_version=file_get_contents( 'http://www.gurugazette.com/ZipPoster/ZipPosterVersionInfo.txt' );
		KBM_ZipPoster_Debug ('attempte to retrieve latest version number');
		if ($kbm_ZipPoster_remote_version == false){
			KBM_ZipPoster_Debug ('bad retrieval');
			// throw an error message and bail
			KBM_ZipPoster_DisplayMessages ( 'error: bad remote version file', -1 );
		}else{
			KBM_ZipPoster_Debug ('good retrieval');
			// make sure remote version information is clean
			if ( preg_match ( '/^\d+(\.\d+)+$/', $kbm_ZipPoster_remote_version ) > 0 ){
				KBM_ZipPoster_Debug ('version number is a valid version number');
				// Check remote version number with current version number
				if ( '1.1.3' != $kbm_ZipPoster_remote_version ) {
					KBM_ZipPoster_Debug ('version number does not match the current version');
					// Issue update notification if new version is available
					KBM_ZipPoster_DisplayMessages ( $kbm_ZipPoster_remote_version, 2 );
				}
			}else{
				KBM_ZipPoster_Debug ('remote version number is not a valid version number');
				// Issue alert that a bad version number was found.
				KBM_ZipPoster_DisplayMessages ( 'error: bad remote version number', -1 );
			}
		}
	}
	
	KBM_ZipPoster_Debug ('KBM_ZipPoster_handle_update_notification - FINISHED');
}

function KBM_ZipPoster_display_options_page ( $kbm_ZipPoster_Configuration ) {
	KBM_ZipPoster_Debug ('KBM_ZipPoster_display_options_page - STARTED');
	global $post;
	$kbm_ZipPoster_catagory_id_array = get_all_category_ids();
?>
<script type="text/javascript">
document.kbm_ZipPoster_Categories = new Array();
<?php
	foreach ($kbm_ZipPoster_catagory_id_array as $kbm_ZipPoster_catagoryID){
		query_posts("showposts=1&cat=".$kbm_ZipPoster_catagoryID);

		while ( have_posts()){
		the_post();}

		echo ('document.kbm_ZipPoster_Categories['.$kbm_ZipPoster_catagoryID.'] = "');
		echo $post->post_date ;
		echo ('";'."\n");
	}
?>


function kbm_ZipPoster_Last_click ( ) {
	document.getElementById ( 'kbm_ZipPoster_Start_Time_Input' ).value = 
		document.kbm_ZipPoster_Categories[
			document.getElementById ( 'kbm_ZipPoster_Category_Select' ).value
		]
	;
}


function kbm_ZipPoster_Now_click ( ) {
	document.getElementById ( 'kbm_ZipPoster_Start_Time_Input' ).value = "<?php echo( date("Y-m-d H:i:s") );?>"
}

function kbm_ZipPoster_Update_Average_Time ( ) {
	var kbm_ZipPoster_earlyNumber = document.getElementById ( 'kbm_ZipPoster_EarlyNumberInput' ).value;
	var kbm_ZipPoster_earlyType = document.getElementById ( 'kbm_ZipPoster_EarlyTypeInput' ).value;
	var kbm_ZipPoster_lateNumber = document.getElementById ( 'kbm_ZipPoster_LateNumberInput' ).value;
	var kbm_ZipPoster_lateType = document.getElementById ( 'kbm_ZipPoster_LateTypeInput' ).value;

	var earlyTime = 0;
	if ( kbm_ZipPoster_earlyType == 'Minutes' ){ earlyTime = kbm_ZipPoster_earlyNumber;}
	else if ( kbm_ZipPoster_earlyType == 'Hours' ){ earlyTime = kbm_ZipPoster_earlyNumber*60;}
	else if ( kbm_ZipPoster_earlyType == 'Days' ){ earlyTime = kbm_ZipPoster_earlyNumber*60*24;}
	else if ( kbm_ZipPoster_earlyType == 'Weeks' ){ earlyTime = kbm_ZipPoster_earlyNumber*60*24*7;}
	
	var lateTime = 0;
	if ( kbm_ZipPoster_lateType == 'Minutes' ){ lateTime = kbm_ZipPoster_lateNumber;}
	else if ( kbm_ZipPoster_lateType == 'Hours' ){ lateTime = kbm_ZipPoster_lateNumber*60;}
	else if ( kbm_ZipPoster_lateType == 'Days' ){ lateTime = kbm_ZipPoster_lateNumber*60*24;}
	else if ( kbm_ZipPoster_lateType == 'Weeks' ){ lateTime = kbm_ZipPoster_lateNumber*60*24*7;}
	
	var averageTime = ((( parseInt(lateTime) - parseInt(earlyTime) ) / 2 ) + parseInt(earlyTime) ) ;
	// Harden the average time calculation
	
	var kbm_ZipPoster_AverageInput = document.getElementById ( 'kbm_ZipPoster_MiddleInput' ).firstChild;
	kbm_ZipPoster_AverageInput.nodeValue = '';
	
	var Weeks = parseInt ( averageTime / (60*24*7) );
	if ( Weeks > 0 ) {
		averageTime = averageTime - ( Weeks * (60*24*7) ) ;
		kbm_ZipPoster_AverageInput.nodeValue = kbm_ZipPoster_AverageInput.nodeValue  + Weeks + ' Weeks ';
	}
	var Days = parseInt ( averageTime / (60*24) );
	if ( Days > 0 ) {
		averageTime = averageTime - ( Days * (60*24) ) ;
		kbm_ZipPoster_AverageInput.nodeValue = kbm_ZipPoster_AverageInput.nodeValue  + Days + ' Days ';
	}
	var Hours = parseInt ( averageTime / ( 60 ) );
	if ( Hours > 0 ) {
		averageTime = averageTime - ( Hours * ( 60 ) ) ;
		kbm_ZipPoster_AverageInput.nodeValue = kbm_ZipPoster_AverageInput.nodeValue  + Hours + ' Hours ';
	}
	if ( averageTime > 0 ) {
		kbm_ZipPoster_AverageInput.nodeValue = kbm_ZipPoster_AverageInput.nodeValue  + averageTime + ' Minutes ';
	}
}

function kbm_ZipPoster_DIVOnMouseOver ( ) {
	if ( this.flag != true){
		this.flag = true;
		kbm_ZipPoster_Update_Average_Time ( );
		kbm_ZipPoster_Last_click ( );
	}
}
</script>
<div class="wrap" onmouseover='kbm_ZipPoster_DIVOnMouseOver();'>
<?php KBM_ZipPoster_DisplayMessages( 'DISPLAY MESSAGES NOW' , 255 ); ?>



<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />

<?php wp_nonce_field('update-options'); ?>

<!-- --------------------------------------------------------------------- -->
<table style='width:66%;margin-left:16.5%'>
 <tr>
  <td colspan="2" style='text-align:center'>
   Shortest
  </td>
  <td style='text-align:center;width:33%'>
   Average
  </td>
  <td colspan="2" style='text-align:center'>
   Longest
  </td>
 </tr>
 <tr>
  <td style='width:20%'>
   <input type="text" id='kbm_ZipPoster_EarlyNumberInput' name='kbm_ZipPoster_EarlyNumberInput' style='width:90%' class='button' value='<?php echo ( $kbm_ZipPoster_Configuration['MinPostTimeNumber'] ); ?>' onchange='kbm_ZipPoster_Update_Average_Time();' onkeyup='kbm_ZipPoster_Update_Average_Time();' onmouseup='kbm_ZipPoster_Update_Average_Time();'>
  </td>
  <td style='width:13%'>
   <select id='kbm_ZipPoster_EarlyTypeInput' name='kbm_ZipPoster_EarlyTypeInput'style='width:100%' class='button' onchange='kbm_ZipPoster_Update_Average_Time();' onkeyup='kbm_ZipPoster_Update_Average_Time();' onmouseup='kbm_ZipPoster_Update_Average_Time();'>
    <option <?php if ($kbm_ZipPoster_Configuration['MinPostTimeType'] == 'Minutes'){echo (' selected="selected" ');}?> value='Minutes'>Minutes</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MinPostTimeType'] == 'Hours'){echo (' selected="selected" ');}?> value='Hours'>Hours</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MinPostTimeType'] == 'Days'){echo (' selected="selected" ');}?> value='Days'>Days</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MinPostTimeType'] == 'Weeks'){echo (' selected="selected" ');}?> value='Weeks'>Weeks</option>
   </select>
  </td>
  <td id='kbm_ZipPoster_MiddleInput' style='text-align:center'>
	3 Hours 30 Minutes
  </td>
  <td style='width:20%'>
   <input type="text" id='kbm_ZipPoster_LateNumberInput' name='kbm_ZipPoster_LateNumberInput' style='width:90%' class='button' value='<?php echo ( $kbm_ZipPoster_Configuration['MaxPostTimeNumber'] ); ?>' onchange='kbm_ZipPoster_Update_Average_Time();' onkeyup='kbm_ZipPoster_Update_Average_Time();' onmouseup='kbm_ZipPoster_Update_Average_Time();'>
  </td>
  <td style='width:13%'>
   <select id='kbm_ZipPoster_LateTypeInput' name='kbm_ZipPoster_LateTypeInput' style='width:100%' class='button' onchange='kbm_ZipPoster_Update_Average_Time();' onkeyup='kbm_ZipPoster_Update_Average_Time();' onmouseup='kbm_ZipPoster_Update_Average_Time();'>
    <option <?php if ($kbm_ZipPoster_Configuration['MaxPostTimeType'] == 'Minutes'){echo (' selected="selected" ');}?> value='Minutes'>Minutes</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MaxPostTimeType'] == 'Hours'){echo (' selected="selected" ');}?> value='Hours'>Hours</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MaxPostTimeType'] == 'Days'){echo (' selected="selected" ');}?> value='Days'>Days</option>
	<option <?php if ($kbm_ZipPoster_Configuration['MaxPostTimeType'] == 'Weeks'){echo (' selected="selected" ');}?> value='Weeks'>Weeks</option>
   </select>
  </td>
 </tr>
</table>

  
<table style='width:66%;margin-left:16.5%'>
 <tr>
  <td style='width:12%'>
   Start Time:
  </td>
  <td style='width:66%'>
	<input type='text' id='kbm_ZipPoster_Start_Time_Input' name='kbm_ZipPoster_Start_Time_Input' value='Hello!' style='width:97%' class='button'/>
  </td>
  <td style='width:11%'>
    <input type='button' value='Now' style='width:100%' class='button-secondary' onclick='kbm_ZipPoster_Now_click();'>
  </td>
  <td style='width:11%'>
    <input type='button' value='Last' style='width:100%' class='button-secondary' onclick='kbm_ZipPoster_Last_click();'>
  </td>
 </tr>
</table>

<table style='width:66%;margin-left:16.5%'>
 <tr>
  <td style='width:11%'>
   Post Title:
  </td>
  <td style='width:57%'>
   <input type='text' name='kbm_ZipPoster_PostTitle' style='width:95%' class='button' />
  </td>
  <td style='width:11%'>
   Catagory:
  </td>
  <td style='width:20%'>
   <select id='kbm_ZipPoster_Category_Select' name='kbm_ZipPoster_Category_Select' style='width:100%;text-align:center' class='button'>
<?php
	$kbm_ZipPoster_catagory_id_array = get_all_category_ids();
	foreach ($kbm_ZipPoster_catagory_id_array as $kbm_ZipPoster_catagoryID){
		echo '<option ';
		if ($kbm_ZipPoster_catagoryID == $kbm_ZipPoster_Configuration['CatagoryID']){
			echo' selected="selected"';
		}
		echo ' value="'.$kbm_ZipPoster_catagoryID.'">'.get_cat_name($kbm_ZipPoster_catagoryID).'</option>';
	}
?>
   </select>
  </td>  
 </tr>
 <tr>
  <td colspan='5'>
   Entry
  </td>
 </tr>
 <tr>
  <td colspan='5'>
   <textarea rows='7' name='kbm_ZipPoster_PostEntry' style='width:98%'  class='button'></textarea>
  </td>
 </tr>
</table>

<table style='width:66%;margin-left:16.5%'>
 <tr width='100%'>
  <td style='width:5%'>
   File:
  </td>
  <td style='width:84%'>
   <input type='file' name='kbm_ZipPoster_File' size='65' style='width:100%' />
  </td>
  <td style='width:11%'>
   <input type="submit" name="Submit" value="<?php _e('Do It') ?>" style='width:100%' class='button-secondary'/>
  </td>
 </tr>
</table>
<table style='width:66%;margin-left:16.5%'>
 <tr>
  <td>
	Automatic Update Notification
  </td>
  <td>
   <select id='kbm_ZipPoster_UpdateNotification' name='kbm_ZipPoster_UpdateNotification' style='width:100%' class='button'>
    <option <?php if ($kbm_ZipPoster_Configuration['UpdateNotification'] == 'true'){echo (' selected="selected" ');}?> value='true'>Enabled</option>
	<option <?php if ($kbm_ZipPoster_Configuration['UpdateNotification'] == 'false'){echo (' selected="selected" ');}?> value='false'>Disabled</option>
   </select>
  </td>
 <tr>
</table>
<!-- --------------------------------------------------------------------- -->
</form>
</div>
<?php
	KBM_ZipPoster_Debug ('KBM_ZipPoster_display_options_page - FINISHED');
}
?>