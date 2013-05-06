(function ($) {

/*
 * Function: fnGetColumnData
 * Purpose:  Return an array of table values from a particular column.
 * Returns:  array string: 1d data array
 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
 *           int:iColumn - the id of the column to extract the data from
 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
 */
jQuery.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
	// check that we have a column id
	if ( typeof iColumn == "undefined" ) return [];

	// by default we only wany unique data
	if ( typeof bUnique == "undefined" ) bUnique = true;

	// by default we do want to only look at filtered data
	if ( typeof bFiltered == "undefined" ) bFiltered = true;

	// by default we do not wany to include empty values
	if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;

	// list of rows which we're going to loop through
	var aiRows;

	// use only filtered rows
	if (bFiltered == true) aiRows = oSettings.aiDisplay; 
	// use all rows
	else aiRows = oSettings.aiDisplayMaster; // all row numbers
 
	// set up data array    
	var asResultData = new Array();

	for (var i=0,c=aiRows.length; i<c; i++) {
		iRow = aiRows[i];
		var sValue = this.fnGetData(iRow, iColumn);

		// ignore empty values?
		if (bIgnoreEmpty == true && sValue.length == 0) continue;
 
		// ignore unique values?
		else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

		// else push the value onto the result data array
		else asResultData.push(sValue);
	}

	return asResultData;
};


/**
 * By default DataTables only uses the sAjaxSource variable at initialisation
 * time, however it can be useful to re-read an Ajax source and have the table
 * update. Typically you would need to use the fnClearTable() and fnAddData()
 * functions, however this wraps it all up in a single function call.
 *  @name fnReloadAjax
 *  @anchor fnReloadAjax
 *  @author <a href="http://sprymedia.co.uk">Allan Jardine</a>
 *
 *  @example
 *    // Example call to load a new file
 *    oTable.fnReloadAjax( 'media/examples_support/json_source2.txt' );
 *     
 *    // Example call to reload from original file
 *    oTable.fnReloadAjax();
 */
$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
{
	if ( typeof sNewSource != 'undefined' && sNewSource != null ) {
		oSettings.sAjaxSource = sNewSource;
	}

	// Server-side processing should just call fnDraw
	if ( oSettings.oFeatures.bServerSide ) {
		this.fnDraw();
		return;
	}

	this.oApi._fnProcessingDisplay( oSettings, true );
	var that = this;
	var iStart = oSettings._iDisplayStart;
	var aData = [];
 
	this.oApi._fnServerParams( oSettings, aData );

	oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
		/* Clear the old information from the table */
		that.oApi._fnClearTable( oSettings );

		/* Got the data - add it to the table */
		var aData =  (oSettings.sAjaxDataProp !== "") ?
			that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;

		for ( var i=0 ; i<aData.length ; i++ )
		{
			that.oApi._fnAddData( oSettings, aData[i] );
		}

		oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();

		if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true )
		{
			oSettings._iDisplayStart = iStart;
			that.fnDraw( false );
		}
		else
		{
			that.fnDraw();
		}

		that.oApi._fnProcessingDisplay( oSettings, false );

		/* Callback user function - for event handlers etc */
		if ( typeof fnCallback == 'function' && fnCallback != null )
		{
			fnCallback( oSettings );
		}
	}, oSettings );
};


// Breadcrumb Feature
$.fn.dataTableExt.aoFeatures.push({
	"fnInit": function( oDTSettings ) {
		return new DT_Breadcrumb( oDTSettings );
	},
	"cFeature": "B",
	"sFeature": "Breadcrumb"
});

var DT_Breadcrumb = function ( oDTSettings )
{
	oDTSettings.aoDrawCallback.push({
		"fn": function () {
			var $breadcrumb = $(oDTSettings.nTableWrapper).find('.breadcrumb'),
				root = oDTSettings.oInit.sStartRoot, // relative root path
				path = oDTSettings.sCurrentPath ? oDTSettings.sCurrentPath : '', // current browsed path
				brcb = [],
				paths;

			// remove the root, is confidential data
			path = path.replace(new RegExp('^'+root), '');

			// clean the path
			path = path.replace(/(^\/|\/$)/, '');

			// split the path in folders
			paths = path.length ? path.split('/') : [];

			// if active path is the root
			if (!paths.length) brcb.push('<li class="active">root</li>');

			// if not
			else brcb.push('<li><a href="#" data-path="">root</a><span class="divider">/</span></li>');

			// populate with the other paths
			path = [];
			$.each(paths, function(i, v){
				path.push(v);
				if (paths.length == i+1 ) {
					brcb.push('<li class="active">' + v + '</li>');
				} else {
					brcb.push('<li><a href="#" data-path="' + path.join('/') + '">' + v + '</a><span class="divider">/</span></li>');
				}
			})

			// update breadcrumb
			$breadcrumb.html(brcb.join(''));
		},
		"sName": "Breadcrumb"
	});

	// create the breadcrumb wrapper
	var breadcrumb = $('<ul class="breadcrumb" />')

	// assign events
	.on('click', 'a', function(e){
		var sUrl = oDTSettings.sAjaxSource;

		// update root values
		oDTSettings.sCurrentPath = oDTSettings.oInit.sStartRoot;
		oDTSettings.sGoToPath = $(this).data('path');

		// reload
		oDTSettings.oInstance.fnReloadAjax(sUrl);
		return false;
	})

	return breadcrumb.get(0);
}


/* ===================================================
 * Bootstrap integration - Custom Styling + Pagination
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */

/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
	"sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
	"sPaginationType": "bootstrap",
	"oLanguage": {
		"sProcessing": '<span class="zlux-loader-circle-big"></span>',
		"sLengthMenu": "_MENU_ records per page"
	},
	"bProcessing": false, // the display processing will be managed externally
	"bLengthChange": false
});

/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
	"sWrapper": "dataTables_wrapper form-horizontal zlux-datatables"
});


/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          oSettings._iDisplayLength === -1 ?
			0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    oSettings._iDisplayLength === -1 ?
			0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
};

/* Pagination display control when no enaugh rows */
var DT_PagingControl = function ( oDTSettings )
{
	oDTSettings.aoDrawCallback.push({
		"fn": function () {
			var bShow = oDTSettings.oInstance.fnPagingInfo().iTotalPages > 1;
			for ( var i=0, iLen=oDTSettings.aanFeatures.p.length ; i<iLen ; i++ ) {
				oDTSettings.aanFeatures.p[i].style.display = bShow ? "block" : "none";
			}
		},
		"sName": "PagingControl"
	});
}

$.fn.dataTableExt.aoFeatures.push({
	"fnInit": function( oDTSettings ) {
		new DT_PagingControl( oDTSettings );
	},
	"cFeature": "P",
	"sFeature": "PagingControl"
});


/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).addClass('pagination').append(
				'<ul>'+
					'<li class="prev disabled"><a class="prev" href="#">&larr; '+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a class="next" href="#">'+oLang.sNext+' &rarr; </a></li>'+
				'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 5;
			var oPaging = oSettings.oInstance.fnPagingInfo();
			var an = oSettings.aanFeatures.p;
			var i, ien, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, ien=an.length ; i<ien ; i++ ) {
				// Remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// Add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// Add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
} );
})(jQuery);