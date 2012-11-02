<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

	return
	'{
		"_mode":{
			"type": "select",
			"label": "PLG_ZLFRAMEWORK_MODE",
			"help": "PLG_ZLFRAMEWORK_FLS_MODE_DESC",
			"specific": {
				"options": {
					"PLG_ZLFRAMEWORK_FILES":"files",
					"PLG_ZLFRAMEWORK_FOLDERS":"folders",
					"PLG_ZLFRAMEWORK_BOTH":"both"
				}
			},
			"default": "files"
		},
		"_default_source":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FLS_DEFAULT_SOURCE",
			"help": "PLG_ZLFRAMEWORK_FLS_DEFAULT_SOURCE_DESC",
			"check_old_value":{
				"id":"_default_file"
			}
		},
		"_extensions":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FLS_EXTENSIONS",
			"help": "PLG_ZLFRAMEWORK_FLS_EXTENSIONS_DESC",
			"default": "png|jpg|gif|bmp|doc|mp3|mov|avi|mpg|zip|rar|gz|pdf"
		},
		"_websafe_mode":{
			"type": "select",
			"label": "PLG_ZLFRAMEWORK_FLS_WEBSAFE_MODE",
			"help": "PLG_ZLFRAMEWORK_FLS_WEBSAFE_MODE_DESC",
			"specific": {
				"options": {
					"UTF-8":"utf-8",
					"ASCII":"ascii"
				}
			}
		},
		"_source_dir":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FLS_DIRECTORY",
			"help": "PLG_ZLFRAMEWORK_FLS_DIRECTORY_DESC"
		},
		"_max_upload_size":{
			"type": "text",
			"label": "PLG_ZLFRAMEWORK_FLS_MAX_UPLOAD_SIZE",
			"help": "PLG_ZLFRAMEWORK_FLS_MAX_UPLOAD_SIZE_DESC||{PHP-MAX_UPLOAD}"
		}
		
		'/* Amazon Integration */.'
		'.(isset($params['s3']) ? ',
		
		"_s3":{
			"type": "checkbox",
			"label": "PLG_ZLFRAMEWORK_FLS_S3_INTEGRATION",
			"default": "0",
			"specific":{
				"label":"PLG_ZLFRAMEWORK_ENABLE"
			},
			"dependents": "s3_content > 1"
		},
		"s3_content":{
			"type": "wrapper",
			"fields": {
				"_s3bucket":{
					"type": "text",
					"label": "PLG_ZLFRAMEWORK_FLS_S3_BUCKET",
					"help": "PLG_ZLFRAMEWORK_FLS_S3_BUCKET_DESC"
				},
				"_awsaccesskey":{
					"type": "password",
					"label": "PLG_ZLFRAMEWORK_FLS_AWS_ACCESKEY",
					"help": "PLG_ZLFRAMEWORK_FLS_AWS_ACCES_DESC"
				},
				"_awssecretkey":{
					"type": "password",
					"label": "PLG_ZLFRAMEWORK_FLS_AWS_SECRETKEY",
					"help": "PLG_ZLFRAMEWORK_FLS_AWS_ACCES_DESC"
				}
			}
		}' : '').'
	}';

?>