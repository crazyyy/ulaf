if (wp_ajax.lang) {
    var lang = JSON.parse(wp_ajax.lang);
} else {
    var lang = [];
}

var SelectMul = (Object.keys(lang).length > 0) ? lang.Select_option : 'Seleccione una Opcion';
var comi = (Object.keys(lang).length > 0) ? lang.Start : 'Comenzar';

var scan = (Object.keys(lang).length > 0) ? lang.Scanning : 'Escaneando';
var querys = (Object.keys(lang).length > 0) ? lang.Query_DB : 'Consultas DB';
var mem = (Object.keys(lang).length > 0) ? lang.Memory_peak : 'Pico de Memoria';
var tTime = (Object.keys(lang).length > 0) ? lang.Request_time : 'Tiempo de Solicitud';

var testResult = (Object.keys(lang).length > 0) ? lang.Results_from_test : 'Resultados de la Prueba';
var betterRes = (Object.keys(lang).length > 0) ? lang.You_will_get_better_result_if_disactive_plugins : 'Obtendras mejores resultados si desactivas plugins';

var elTime = (Object.keys(lang).length > 0) ? lang.Time : 'Tiempo';
var nom = (Object.keys(lang).length > 0) ? lang.Name : 'Nombre';
var resultado = (Object.keys(lang).length > 0) ? lang.Result : 'Resultado';

var grafT = (Object.keys(lang).length > 0) ? lang.Execution_time : 'Tiempo de Ejecucion';
var resultado = (Object.keys(lang).length > 0) ? lang.Plugin_execution_time_in_mileseconds : 'Tiempo de ejecución en milisegundos';

var pagi = (Object.keys(lang).length > 0) ? lang.Page : 'Página';
var loadT = (Object.keys(lang).length > 0) ? lang.Load_time : 'Tiempo de Carga';


document.addEventListener("DOMContentLoaded", function () {
   let deactivateLinks = document.querySelectorAll('.deactivate a');

   deactivateLinks.forEach(link => {
	   link.addEventListener('click', function (event) {

		   if ( link.getAttribute('id') === "deactivate-profiling-tool-for-wp" ) {

			   event.preventDefault(); // Evita la desactivación inmediata

			   let modal = document.getElementById('plugin-deactivation-modal');
			   modal.style.display = 'flex';

			   // Botón de confirmar desactivación
			   document.getElementById('confirm-deactivate').addEventListener('click', function () {
				   window.location.href = link.href; // Procede con la desactivación
			   });

			   // Botón para ir al formulario externo
			   document.getElementById('go-to-form').addEventListener('click', function () {
				   window.open('https://www.tbplugin.com/desinstalacion-plugin/', '_blank');
			   });

			   // Botón de cancelar
			   document.querySelector('.cancel-button').addEventListener('click', function () {
				   modal.style.display = 'none';
			   });

		   }

	   });
   });
}); 


(function( $ ) {
	
	var patp_allow_run = true;
	var plugin_element = '';


	 $(document).ready( function() {

        addListeners();

        if (typeof wp_ajax !== 'undefined') {
	        if (wp_ajax.tab_exists) {
	            document.getElementById("OptionTab").click();
	        } else {
	            document.getElementById("defaultOpen").click();
	        }
	    }

		var Htabla = $('#historyTable');

		$.ajax({
			url: wp_ajax.ajaxurl,
			data: {
				'action': 'load_table_data',
				'security' : wp_ajax.ajax_nonce
			},
			dataType: 'json',
			success: function( data ) {
				// Inicializa DataTables con los datos recibidos
				Htabla.DataTable({
					data: data,
					columns: [
						{ data: 'nombre' },
						{ data: 'fecha' },
						{ data: 'tipo' },
						{ data: 'items' },
						{ data: 'tiempo' },
						{ data: 'memory' },
						{ data: 'queries' },
					],
					columnDefs: [
						{
							targets: 1,
							data: 'fecha',
							render: function (data, type, row, meta) {
								var datePart = data.match(/\d+/g),
								year = datePart[0].substring(0,4),
								month = datePart[1], day = datePart[2];
								return day+'/'+month+'/'+year;
							}
						}
					],
					language: {
                        "lengthMenu": lang.lengthMenu,
                        "zeroRecords": lang.zeroRecords,
                        "info": lang.info,
                        "infoEmpty": lang.infoEmpty,
                        "infoFiltered": lang.infoFiltered,
                        "search": lang.search,
                        "paginate": {
                            "first":      lang.first,
                            "last":       lang.last,
                            "next":       lang.next,
                            "previous":   lang.previous
                        }
                    }
				});
			}
		});
		 
		$('#pageSelected').select2({
            placeholder: SelectMul,
        });
		 	 
    });
	
	$( document ).ajaxComplete(function() {

        // Remove any existing listeners first
        $('#saveProfile').off( 'click' );
		$('#run_quick_page_tests').off( 'click' );
		$('#run_quick_tests').off( 'click' );
		
        addListeners();
    });
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 
	 function addListeners() {
		 
		"use strict";
		 	
		$('#saveProfile').on( "click", function( event ) {
			
			event.preventDefault();
			
            let lang = $("input[name='langSelect']:checked").val();
            let orden = $("input[name='tableOrd']:checked").val();

            $.ajax({
                url: wp_ajax.ajaxurl,
                data: {
                    'action': 'save_plugin_options',
                    'language' : lang,
                    'table_ord' : orden,
                    'security' : wp_ajax.ajax_nonce
                },
                success: function ( response ) {
						
						alert('Opciones Guardadas');
						console.log( response );
                    	location.reload();	
				
                },
				error: function( errorThrown ){
					alert( errorThrown );
                    console.log( errorThrown );
                }
            });

        });
		 
		 
		 $('#run_quick_page_tests').on( "click", function() {
            var paginas = [];

            paginas = $('#pageSelected').val();

            if (paginas.length < 2) {

               if(patp_allow_run == false)return false;

                patp_allow_run = false;

                $('.lds-ellipsis').removeClass('hidden');
                
                var page = $('#pageSelected').val();

                medirTiempoCarga(page, function(tiempoCarga) {
                    $('.lds-ellipsis').addClass('hidden');
                    
                    $('#pageTest').append(makePageTableHtml(page, tiempoCarga));

                    savePageProfile(page, tiempoCarga);

                    patp_allow_run = true;

                    reloadTableData();

                });

            } else {

                if(patp_allow_run == false)return false;

                patp_allow_run = false;

                $('.lds-ellipsis').removeClass('hidden');

                paginas.forEach(function(pagina, index) {
                    
                   medirTiempoCarga(pagina, function(tiempoCarga) {
                        $('.lds-ellipsis').addClass('hidden');
                        
                        $('#pageTest').append(makePageTableHtml(pagina, tiempoCarga));

                        savePageProfile(pagina, tiempoCarga);
 
                    }); 

                });

                patp_allow_run = true;

                reloadTableData();

            }

        });
		
		 $('#run_quick_tests').on( "click", function() {
            if(patp_allow_run == false)return false;

            var run_button_element = $(this);

            //run_button_element.hide();

            $('#run_quick_tests').html('<span style="font-size: 16px;">' + scan + '...<div class="loader"></div></span>');

            $('.lds-ellipsis').removeClass('hidden');

            $('.patp table span.result').html('');
            $('#quick_test_report').html('');
            $('#quick_test_report').css('display', 'none');

            patp_allow_run = false;
            var elements_all_plugins = $("table.wp-list-table a.patp_run_test");
            var current_element = -1;

            var myInterval = setInterval(function() 
                                         {
                if(current_element == elements_all_plugins.length-1)
                {
                    //console.log('run_quick_tests END');
                    $('.lds-ellipsis').addClass('hidden');
                    
                    patp_allow_run = true;
                    //run_button_element.show();
                    $('#run_quick_tests').html(comi);

                    generate_report();
                    clearInterval(myInterval);
                    return false;
                }
                current_element++;
                run_test_specific(elements_all_plugins.eq(current_element));
            }, 1500);

            return false;
        });
		 
		$('a.patp_run_test').on( "click", function(e){

			e.preventDefault();

            if(patp_allow_run == false)return false;

            patp_allow_run = false;

            console.log($(this));
            plugin_element = $(this);

            plugin_element.parent().parent().find('span.result').first().html('');
            $('.lds-ellipsis').removeClass('hidden');

            setTimeout(function() {
                run_test_specific(plugin_element);
                $('.lds-ellipsis').addClass('hidden');
                patp_allow_run = true;
            }, 100);

            return false;
        });
		 
	 } // End Add Listeners Function
	
})( jQuery );

function time() {
    var timestamp = Math.floor(new Date().getTime() / 1000)
    return timestamp;
}

function openTab(evt, cityName) {
	// Declare all variables
	var i, tabcontent, tablinks;

	// Get all elements with class="tabcontent" and hide them
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}

	// Get all elements with class="tablinks" and remove the class "active"
	tablinks = document.getElementsByClassName("tablinks");
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	}

	// Show the current tab, and add an "active" class to the button that opened the tab
	document.getElementById(cityName).style.display = "block";
	evt.currentTarget.className += " active";
}

function medirTiempoCarga(url, callback) {
	var inicio = performance.now();
	var tiempoCarga = 0;

	jQuery.ajax({
		url: url,
		success: function() {
			var fin = performance.now();
			tiempoCarga = fin - inicio;
			callback(tiempoCarga);
		},
		error: function( errorThrown ){
			alert( errorThrown );
			console.log( errorThrown );
		}
	});
}

function run_test_specific(plugin_element) {
    patp_allow_run = false;

    var plugin_file = plugin_element.parent().parent().find('span.plugin_name').text();
    var resultSpan = plugin_element.parent().parent().find('span.result').first();
    var resultColor = plugin_element.parent().parent().find('span.color').first();
    var queries_number = '';

    // 1. Disable only selected plugin
    jQuery.ajax({
        url: wp_ajax.homeUrl +'?ptfwp=1&plugin='+plugin_file+'&time=' + time(), // time at end is added because of possible caching
        type: "GET",
        success: function (result) {
        },
        async: false
    }).done(function () {
    });

    // 2. Run test

    var ajaxTime= new Date().getTime();

    jQuery.ajax({
        url: wp_ajax.homeUrl + '?ptfwp=3&time=' + time(), // time at end is added because of possible caching
        type: "GET",
        success: function (result) {
            var findStringQuery = "[QUERIES_NUMBER]";
            var startStringQuery = result.indexOf("[QUERIES_NUMBER]")+findStringQuery.length;
            var endStringQuery   = result.indexOf("[/QUERIES_NUMBER]");
            var queriesNumber = 0;

            if (startStringQuery >= 0)
            {
                queriesNumber = result.substr(startStringQuery, endStringQuery-startStringQuery);
                queries_number+= ', ' + querys + ': ' + queriesNumber;
            }

            var findStringPMemory = "[PEAK_MEMORY_USAGE]";
            var startStringPMemory = result.indexOf("[PEAK_MEMORY_USAGE]")+findStringPMemory.length;
            var endStringPMemory   = result.indexOf("[/PEAK_MEMORY_USAGE]");
            var pMemory = 0;

            if (startStringPMemory >= 0)
            {
                pMemory = result.substr(startStringPMemory, endStringPMemory-startStringPMemory);
                queries_number+= ', ' + mem + ': ' + pMemory+' MB';
            }

            if(plugin_file == 'ALL')
            {
                jQuery('#max_db').html(queriesNumber);
                jQuery('#max_mem').html(pMemory);
            }
        },
        async: false
    }).done(function () {
        var totalTime = new Date().getTime()-ajaxTime;

        if(plugin_file == 'ALL')jQuery('#max_time').html(totalTime);

        var max_time = jQuery('#max_time').html();

        resultSpan.parent().find('span.timing').html(totalTime);
        resultSpan.html(tTime + ': '+totalTime+'ms'+queries_number);

        if(plugin_file == 'ALL' || plugin_file == 'NONE')
        {
            resultSpan.removeClass('orange');
            resultSpan.removeClass('red');
            resultSpan.removeClass('green');

            if(totalTime < 1000){
                resultSpan.addClass('green');resultColor.html('green')
            } else if(totalTime < 2000){
                resultSpan.addClass('orange');resultColor.html('orange')
            } else {
                resultSpan.addClass('red');resultColor.html('red')
            }

        } else {

            resultSpan.removeClass('orange');
            resultSpan.removeClass('red');
            resultSpan.removeClass('green');

            if (totalTime < 1000){
                resultSpan.addClass('green');resultColor.html('green')
            } else if(totalTime < 1400){
                resultSpan.addClass('orange');resultColor.html('orange')
            } else {
                resultSpan.addClass('red');resultColor.html('red')
            }
        }
    });

    // 3. re-enable all backed-up plugins list

    jQuery.ajax({
        url: wp_ajax.homeUrl + '?ptfwp=2&plugin='+plugin_file+'&time=' + time(), // time at end is added because of possible caching
        type: "GET",
        success: function (result) {
        },
        async: false
    }).done(function () {
    });
}


function generate_report() {
	var reportText = '';

	reportText+= '<h3>' + testResult + '</h3>';
	reportText+= betterRes + ':<br /><br />';

	var results = [];

	jQuery("table.wp-list-table a.patp_run_test").each(function(){

		plugin_element = jQuery(this);

		var niceName = plugin_element.parent().parent().find('span.nice_name').text();
		var resultSpan = plugin_element.parent().parent().find('span.result').first().html();
		var resultTiming = plugin_element.parent().parent().find('span.timing').first().html();
		var resultColor = plugin_element.parent().parent().find('span.color').first().html();

		results.push({timing: resultTiming, name: niceName, results: resultSpan, color: resultColor});
	});

	results.sort(function (x, y) {
		return x.timing - y.timing;
	});

	jQuery('#quick_test_report').html(reportText+makeTableHTML(results));
	jQuery('#quick_test_report').css('display', 'inline-block');

	makeGrafics(results);
	saveProfile(results);

	jQuery('#resultTable').DataTable({
		paging: false,
		searching: false,
		info: false,
		data: results,
		columns: [
			{ data: 'timing' },
			{ data: 'name' },
			{ data: 'results' },
		],
		order: [0, 'ASC'],
	});

}

function makePageTableHtml(url, time) {

	var pageName = jQuery('#pageSelected').val();

	var color; 

	if(time < 1000){
		color = "green";
	} else if(time < 2000){
		color = "orange";
	} else {
		color = "red";
	}

	var result = '<table width="100%" style="margin-top: 15px;">';
	result += "<tbody>";
	result += '<tr style="text-align: center; font-size: 14px;">';
	result += '<td><b>' + pagi + '</b></td>';
	result += '<td><b>' + loadT + ' (s):</b></td>';
	result += '</tr>';
	result += '<tr style="text-align: center;">';
	result += '<td>'+url+'</td>';
	result += '<td class="'+color+'">'+ Math.round(time)/1000 +' seg</td>';     
	result +=  '</tr>';   
	result += '</tbody>';
	result += '</table>';

	return result;

}

function saveProfile ( data ) {

	const date = new Date();
	let currentDay= String(date.getDate()).padStart(2, '0');
	let currentMonth = String(date.getMonth()+1).padStart(2,"0");
	let currentYear = date.getFullYear();
	let currentDate = `${currentYear}-${currentMonth}-${currentDay}`;

	var object = data.length;
	var fecha = Date.now();
	var tipo = 'Plugins y Temas';
	var queries = jQuery('#max_db').html();
	var memory = jQuery('#max_mem').html();
	var nombre = jQuery('#profile_name').val();
	var elapseTime = jQuery('#max_time').html();

	jQuery.ajax({
		type: 'POST',
		url: wp_ajax.ajaxurl,
		data: {
			'action' : 'save_profile',
			'name': nombre,
			'fecha' : currentDate,
			'tipo' : tipo,
			'items' : object,
			'time' : elapseTime,
			'sql' : queries,
			'memory' : memory,
			'info' : JSON.stringify(data),
			'security' : wp_ajax.ajax_nonce
		},
		success: function (result) {

			console.log('Datos guardados en el historial');
			reloadTableData();

		}
	});

}

function savePageProfile(url, tiempo) {

	const date = new Date();
	let currentDay= String(date.getDate()).padStart(2, '0');
	let currentMonth = String(date.getMonth()+1).padStart(2,"0");
	let currentYear = date.getFullYear();
	let currentDate = `${currentYear}-${currentMonth}-${currentDay}`;

	var nombre = jQuery('#page_profile_name').val();
	var tipo = "Pagina del Sitio";
	var objetos = "1";
	var time = tiempo;

	jQuery.ajax({
		url: wp_ajax.ajaxurl,
		data: {
			'action': 'save_page_profile',
			'nombre' : nombre,
			'fecha' : currentDate,
			'tipo' : tipo,
			'items' : objetos,
			'time' : time,
			'security' : wp_ajax.ajax_nonce
		},
		success: function ( response ) {

			console.log('Inserted ID:' + response);
		    reloadTableData();

		},
		error: function( errorThrown ){
			alert( errorThrown );
			console.log( errorThrown );
		}
	});

}

function reloadTableData() {
	// Realiza la petición AJAX para cargar los nuevos datos
	jQuery.ajax({
		type: 'POST',
		url: wp_ajax.ajaxurl,
		data: {
			'action': 'load_table_data',
			'security' : wp_ajax.ajax_nonce
		},
		dataType: 'json',
		success: function( data ) {
		// Obtén la referencia a la tabla
		var table = jQuery('#historyTable').DataTable();

		// Limpia la tabla existente
		table.clear();

		// Agrega los nuevos datos a la tabla
		table.rows.add(data);

		// Dibuja la tabla con los nuevos datos
		table.draw();
	}
});
}

function makeTableHTML( myArray ) {

	var result = "<table id='resultTable' border=1 width='100%'>";

	result += "<thead>";
	result += "<tr>";
	result += "<td><b>" + elTime + "</b></td>";
	result += "<td style='text-align:center'><b>" + nom + "</b></td>";
	result += "<td style='text-align:center'><b>" + resultado + "</b></td>";
	result += "</tr>";
	result += "</thead>";

	jQuery.each( myArray, function(){

		result += "<tr>";
		myObject = jQuery(this)[0];
		for (var p in myObject) {
			if(p != 'color')
				result += "<td class='"+(myObject['color']=='green'?'':myObject['color'])+"'>"+myObject[p]+"</td>";
		}
		console.log(myObject);
		result += "</tr>";
	});
	result += "</table>";
	return result;


}

const ctx = document.getElementById('myChart');

function makeGrafics(array) {

	var resultName = [];
	var resultTime = [];

	document.getElementById('timeChart').style.display = "block";

	jQuery.each( array, function(){

		myObject = jQuery(this)[0];

		resultName.push(myObject['name']);
		resultTime.push(myObject['timing']);

	});


	new Chart(ctx, {
		type: 'bar',
		data: {
			labels: resultName,
			datasets: [{
				label: grafT,
				data: resultTime,
				borderWidth: 1
			}]
		},
		title: {
			display: true,
			text: resultado
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			},
			responsive: true,
			maintainAspectRatio: false
		}
	});

}



