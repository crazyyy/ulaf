// WPGear. Plugin Notes Label
// plugin_note_label_updatecore.js

	var PluginNotesLabel_User_Name 			= PluginNotesLabel_VarObject.user;
	var PluginNotesLabel_Setup_ShowAuthor 	= PluginNotesLabel_VarObject.show_author;
	var PluginNotesLabel_Setup_ShowDate 	= PluginNotesLabel_VarObject.show_date;
	var PluginNotesLabel_Ajax_Nonce			= PluginNotesLabel_VarObject._wpnonce;

	window.addEventListener ('load', function() {
		var Plugins = new Array ();
		var Table_Update_Plugins = document.getElementById("update-plugins-table");		
		
		if (Table_Update_Plugins) {
			// Message Box
			var Table_Message_Box = document.createElement("div");		
			Table_Message_Box.setAttribute("id", "pluginnotelabel-messagebox");
			Table_Message_Box.setAttribute("class", "pluginnotelabel-messagebox");
			
			Table_Message_Box.innerHTML = '... get Notes processing ...';
			Table_Update_Plugins.insertAdjacentElement("beforeBegin", Table_Message_Box);			
			
			// Table List
			
			for (i = 0; i < Table_Update_Plugins.tBodies[0].children.length; i++) {
				var Plugin = Table_Update_Plugins.tBodies[0].children[i];	
				// var Plugin_Name = Plugin.getElementsByTagName('strong')[0].innerHTML;

					// var ID = Plugin_Name.toLowerCase();
					// ID = ID.replace(/[^a-z0-9]+/gi, "_");
				
				Plugin.setAttribute("id", "plugin-notes-row_" + i);

				var Plugin_CheckColumn_Item = Plugin.getElementsByClassName("check-column")[0];	

				if (Plugin_CheckColumn_Item.childElementCount > 0) {
					var Plugin_Base = Plugin_CheckColumn_Item.children[0].value;
					var Plugin_Slug = Plugin_Base.split('/')[0];
					
					Plugins.push(Plugin_Slug);
				} else {
					// The element has no child nodes. May be this Plugin Can't Update.
					
					var Plugin_Slug = 'update disable';
					
					Plugins.push(Plugin_Slug);
				}
			}
				
			// console.log(Plugins);
			
			var PluginNoteLabel_Ajax_URL = ajaxurl;
			var PluginNoteLabel_Ajax_Data = 'action=plugin_note_label&mode=get_notes&_wpnonce=' + PluginNotesLabel_Ajax_Nonce + '&names=' + Plugins;
			
			jQuery.ajax({
				type:"POST",
				url: PluginNoteLabel_Ajax_URL,
				dataType: 'json',
				data: PluginNoteLabel_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	
					console.log(Obj_Request);
					
					var Status	= Obj_Request.status;
					var Answer 	= Obj_Request.answer;					
					var Notes  	= Obj_Request.notes;
					
					if (Notes) {
						for (i = 0; i < Notes.length; i++) {
							var Note_User 		= Notes[i]['user'];
							var Note_Content 	= Notes[i]['content'];
							var Note_Date 		= Notes[i]['date'];
							var Plugin_Slug 	= Notes[i]['slug'];
							
							var Plugin_Box = document.getElementById('plugin-notes-row_' + i);
							


							var Plugin_Note_Label = "Note";	
							
							if (Note_Content) {
								if (PluginNotesLabel_Setup_ShowAuthor == '1') {
									Plugin_Note_Label += " [" + Note_User + "]";
								}

								if (PluginNotesLabel_Setup_ShowDate == '1') {
									Plugin_Note_Label += " <span class='pluginnotelabel-label-date'>" + Note_Date + "</span>";
								}

								var Note_Title = "";
								if (!PluginNotesLabel_Setup_ShowAuthor && $PluginNotesLabel_Setup_ShowDate) {
									Note_Title = "Note by [" + Note_User + "] " + Note_Date;
								}
								
							} else {
								Note_Content = '';
							}

							Plugin_Note_Label += ":";					
									
							var Note = '';
							
							var Note_Box = document.createElement("tr");						
									
							if (Note_User == '*') {
								// May be this Plugin Can't Update.
								
								Note_Box.setAttribute("id", "pluginnotelabel-box_update_disabed_" + i);
								
								Plugin_Box.insertAdjacentElement("afterend", Note_Box);	
								
								Note +=	'<td colspan ="100%" class="pluginnotelabel-box">';
								Note +=	Plugin_Note_Label;
								Note +=	'<span id="pluginnotelabel_update_disabed_' + i + '" class="pluginnotelabel-content" title="' + Note_Title + '">';
								Note +=	Note_Content;
								Note +=	'</span>';
								Note +=	'</td>';
														
								Note_Box.innerHTML = Note;

							} else {
								Note_Box.setAttribute("id", "pluginnotelabel-box_" + Plugin_Slug);
								
								Plugin_Box.insertAdjacentElement("afterend", Note_Box);	
								
								Note +=	'<td colspan ="100%" class="pluginnotelabel-box">';
								Note +=	'<span id="pluginnotelabel_control_' + Plugin_Slug + '" class="pluginnotelabel-label" title="Click to Edit Note">';
								Note +=	Plugin_Note_Label;
								Note +=	'</span>';
								Note +=	'<span id="pluginnotelabel_' + Plugin_Slug + '" class="pluginnotelabel-content" title="' + Note_Title + '">';
								Note +=	Note_Content;
								Note +=	'</span>';
								Note +=	'</td>';
														
								Note_Box.innerHTML = Note;

								document.getElementById("pluginnotelabel_control_" + Plugin_Slug).setAttribute('onclick','plugin_note_label_edit("' + Plugin_Slug + '", "' + Note_Content + '")');
							}

// console.log(Notes[i]);							
						}
					}
					Table_Message_Box.style.display = 'none';
				}
			});			
		}
	});