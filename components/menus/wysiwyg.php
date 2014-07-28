<menu type="context" id="wysiwyg_menu">
	<!--https://developer.mozilla.org/en-US/docs/Midas-->
	<menu label="Text Style">
		<menuitem label="Bold" data-editor-command="bold"></menuitem>
		<menuitem label="Italics" data-editor-command="italic"></menuitem>
		<menuitem label="Underline" data-editor-command="underline"></menuitem>
		<menuitem label="Strike Through" data-editor-command="strikethrough"></menuitem>
		<menuitem label="Big" data-editor-command="big"></menuitem>
		<menuitem label="Small" data-editor-command="small"></menuitem>
		<menuitem label="Superscript" data-editor-command="superscript"></menuitem>
		<menuitem label="Subscript" data-editor-command="subscript"></menuitem>
	</menu>
	<menu label="Font">
		<menuitem label="Larger" data-editor-command="increasefontsize"></menuitem>
		<menuitem label="Smaller" data-editor-command="decreasefontsize"></menuitem>
		<menu label="Font Family">
			<menuitem label="Alice" data-editor-command="fontname" data-editor-value="Alice"></menuitem>
			<menuitem label="Web Symbols" data-editor-command="fontname" data-editor-value="Web Symbols"></menuitem>
			<menuitem label="Acme" data-editor-command="fontname" data-editor-value="Acme"></menuitem>
			<menuitem label="GNUTypewriter" data-editor-command="fontname" data-editor-value="GNUTypewriter"></menuitem>
			<menuitem label="PressStart" data-editor-command="fontname" data-editor-value="PressStart"></menuitem>
			<menuitem label="GNUTypewriter" data-editor-command="fontname" data-editor-value="GNUTypewriter"></menuitem>
			<menuitem label="Other?" data-editor-command="fontname" data-prompt="What font would you like to use?"></menuitem>
		</menu>
	</menu>
	<menu label="Selection">
		<menuitem label="Select All" data-editor-command="selectall"></menuitem>
		<menuitem label="Clear Formatting" data-editor-command="removeformat"></menuitem>
	</menu>
	<menu label="Indentation">
		<menuitem label="Increase" data-editor-command="indent"></menuitem>
		<menuitem label="Decrease" data-editor-command="outdent"></menuitem>
	</menu>
	<menu label="Justify">
		<menuitem label="Center" data-editor-command="justifycenter"></menuitem>
		<menuitem label="Left" data-editor-command="justifyleft"></menuitem>
		<menuitem label="Right" data-editor-command="justifyright"></menuitem>
		<menuitem label="Full" data-editor-command="justifyfull"></menuitem>
	</menu>
	<menu label="Create">
		<menu label="Headings">
			<menuitem label="H1" data-editor-command="heading" data-editor-value="H1"></menuitem>
			<menuitem label="H2" data-editor-command="heading" data-editor-value="H2"></menuitem>
			<menuitem label="H3" data-editor-command="heading" data-editor-value="H3"></menuitem>
			<menuitem label="H4" data-editor-command="heading" data-editor-value="H4"></menuitem>
			<menuitem label="H5" data-editor-command="heading" data-editor-value="H5"></menuitem>
			<menuitem label="H6" data-editor-command="heading" data-editor-value="H6"></menuitem>
		</menu>
		<menu label="List">
			<menuitem label="Ordered" data-editor-command="insertorderedlist"></menuitem>
			<menuitem label="Unordered" data-editor-command="insertunorderedlist"></menuitem>
		</menu>
		<menuitem label="Link" data-editor-command="createlink" data-prompt="Enter link location"></menuitem>
		<menuitem label="Image" data-editor-command="insertimage" data-prompt="Enter image location"></menuitem>
		<menuitem label="Code" data-editor-command="inserthtml" data-editor-value="<code>&nbsp;&nbsp;&nbsp;</code>"></menuitem>
		<menuitem label="Custom HTML" data-editor-command="inserthtml" data-prompt="Enter the HTML to insert."></menuitem>
	</menu>
	<menu label="History">
		<menuitem label="Undo" data-editor-command="undo"></menuitem>
		<menuitem label="Redo" data-editor-command="redo"></menuitem>
	</menu>
</menu>