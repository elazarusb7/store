CKEDITOR.dialog.add('btscolumn', function (editor) {
	return {
		title: 'Column',
		minWidth: 300,
		minHeight: 150,
		resizable: false,
		contents: [
			{
				id : 'tab1',
				label : 'First Tab',
				title : 'First Tab',
				elements :[
					
						{
							id: 'text',
							type: 'text',
							label: "Text",
							validate: CKEDITOR.dialog.validate.notEmpty( "Text field cannot be empty." ),
							setup: function (widget) {
								this.setValue(widget.data.text || '');
							},
							commit: function (widget) {
								widget.setData('text', this.getValue());
							}
						}
					
				]
			}
		],
		onOk: function() {
			var text = this.getValueOf('tab1', 'text');
			editor.insertHtml('[column]'+text+'[/column]');
		}
	};
});