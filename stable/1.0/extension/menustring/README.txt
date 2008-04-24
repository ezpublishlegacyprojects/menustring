Solves:
Problem of cutted/shortend menu items by browsers. IE and Firefix behave different.
Includes also a str_replace operator.

Usage in a the edit module:
Textline( used for menu appearance ): Title of a<br> content<dash-br>object 

Usage in a template(menu):
{node_view_gui view=menuitem content_node=$node_object}

Usage in template override for node_object:
{$node.object.data_map.name.content.menu_text|wash()|str_replace( '&lt;br /&gt;', '<br />' )}

Result in menu (frontend):
Title of a
content-
object

Regular result( e.g admin interface ):
Title of a content-object

Setup:

1.) Extension setup
add to "settings/siteaccess/siteaccessname/site.ini.append.php"

[ExtensionSettings]
ActiveExtensions[]=menustring

2.) Query database.

# In this example we turn the attribute "name" of a "folder" into a "menustring" type.
BEGIN WORK;
UPDATE ezcontentclass_attribute SET data_type_string="menustring" WHERE id in ( 4 ) and data_type_string="ezstring";
UPDATE ezcontentobject_attribute SET data_type_string="menustring" WHERE contentclassattribute_id in ( 4 ) and data_type_string="ezstring";
COMMIT;


