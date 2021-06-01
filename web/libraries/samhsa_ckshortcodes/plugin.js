(function () {
	CKEDITOR.plugins.add('samhsa_ckshortcodes', {
		lang: [ 'en'],
		init: function (editor) {
            //--------------ok
            editor.addCommand('btsalert', new CKEDITOR.dialogCommand('btsalert', {}));
            CKEDITOR.dialog.add('btsalert', this.path + 'dialogs/btsalert.js');
            editor.ui.addButton('btsalert', {
                label : "Alert",
                toolbar : 'insert',
                command : 'btsalert',
                icon : this.path + 'icons/btsalert.png'
            });

            //--------------ok
            editor.addCommand('btscolumn', new CKEDITOR.dialogCommand('btscolumn', {}));
            CKEDITOR.dialog.add('btscolumn', this.path + 'dialogs/btscolumn.js');
            editor.ui.addButton('btscolumn', {
                label : "Column",
                toolbar : 'insert',
                command : 'btscolumn',
                icon : this.path + 'icons/btscolumn.png'
            });

            //--------------ok
            editor.addCommand('btsdivider', new CKEDITOR.dialogCommand('btsdivider', {}));
            CKEDITOR.dialog.add('btsdivider', this.path + 'dialogs/btsdivider.js');
            editor.ui.addButton('btsdivider', {
                label : "Divider",
                toolbar : 'insert',
                command : 'btsdivider',
                icon : this.path + 'icons/btsdivider.png'
            });

            //--------------ok
            editor.addCommand('btspanel', new CKEDITOR.dialogCommand('btspanel', {}));
            CKEDITOR.dialog.add('btspanel', this.path + 'dialogs/btspanel.js');
            editor.ui.addButton('btspanel', {
                label : "Panel",
                toolbar : 'insert',
                command : 'btspanel',
                icon : this.path + 'icons/btspanel.png'
            });

            //--------------ok
            editor.addCommand('btsraw', new CKEDITOR.dialogCommand('btsraw', {}));
            CKEDITOR.dialog.add('btsraw', this.path + 'dialogs/btsraw.js');
            editor.ui.addButton('btsraw', {
                label : "Raw",
                toolbar : 'insert',
                command : 'btsraw',
                icon : this.path + 'icons/btsraw.png'
            });
            //--------------ok
            editor.addCommand('btssuccess', new CKEDITOR.dialogCommand('btssuccess', {}));
            CKEDITOR.dialog.add('btssuccess', this.path + 'dialogs/btssuccess.js');
            editor.ui.addButton('btspsuccess', {
                label : "Success",
                toolbar : 'insert',
                command : 'btssuccess',
                icon : this.path + 'icons/btssuccess.png'
            });
            //**************begin core buttons
			//--------------ok
			editor.addCommand('btsalerts', new CKEDITOR.dialogCommand('btsalerts', {}));
			CKEDITOR.dialog.add('btsalerts', this.path + 'dialogs/btsalerts.js');
			editor.ui.addButton('btsalerts', {
				label : "Alerts",
				toolbar : 'insert',
				command : 'btsalerts',
				icon : this.path + 'icons/btsalerts.png'
			});
		
			//--------------ok

			editor.addCommand('btsaccordions', {
			   exec: function(){
					editor.insertHtml('[accordions ] [/accordions]');
			   }
			});
			editor.ui.addButton('btsaccordions', {
				label : "Accordions",
				toolbar : 'insert',
				command : 'btsaccordions',
				icon : this.path + 'icons/btsaccordions.png'
			});
				// --------------ok

			editor.addCommand('btsaccordion', new CKEDITOR.dialogCommand('btsaccordion', {}));
			CKEDITOR.dialog.add('btsaccordion', this.path + 'dialogs/btsaccordion.js');
			editor.ui.addButton('btsaccordion', {
				label : "Accordion item",
				toolbar : 'insert',
				command : 'btsaccordion',
				icon : this.path + 'icons/btsaccordion.png'
			});
			//-------------- ok
			// editor.addCommand('btshr', new CKEDITOR.dialogCommand('btshr', {}));
			// CKEDITOR.dialog.add('btshr', this.path + 'dialogs/btshr.js');
			editor.addCommand('btshr', {
			   exec: function(){
					editor.insertHtml('[hr]');
			   }
			});
			editor.ui.addButton('btshr', {
				label : "HR",
				toolbar : 'insert',
				command : 'btshr',
				icon : this.path + 'icons/btshr.png'
			});
			//--------------
			editor.addCommand('btsjumbotron', new CKEDITOR.dialogCommand('btsjumbotron', {}));
			CKEDITOR.dialog.add('btsjumbotron', this.path + 'dialogs/btsjumbotron.js');
			editor.ui.addButton('btsjumbotron', {
				label : "Jumbotron",
				toolbar : 'insert',
				command : 'btsjumbotron',
				icon : this.path + 'icons/btsjumbotron.png'
			});		
			//--------------
			editor.addCommand('btsprogress', new CKEDITOR.dialogCommand('btsprogress', {}));
			CKEDITOR.dialog.add('btsprogress', this.path + 'dialogs/btsprogress.js');			
			editor.ui.addButton('btsprogress', {
				label : "Progress Bar",
				toolbar : 'insert',
				command : 'btsprogress',
				icon : this.path + 'icons/btsprogress.png'
			});	
			//--------------
			editor.addCommand('btsrow', {
			   exec: function(){
					editor.insertHtml('[row][/row]');
			   }
			});	
			editor.ui.addButton('btsrow', {
				label : "Row",
				toolbar : 'insert',
				command : 'btsrow',
				icon : this.path + 'icons/btsrow.png'
			});			
						//--------------
			editor.addCommand('btscol', new CKEDITOR.dialogCommand('btscol', {}));
			CKEDITOR.dialog.add('btscol', this.path + 'dialogs/btscol.js');
			editor.ui.addButton('btscol', {
				label : "Col",
				toolbar : 'insert',
				command : 'btscol',
				icon : this.path + 'icons/btscol.png'
			});
			
		}
	});
})();