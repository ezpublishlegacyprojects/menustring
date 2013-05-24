<?php

class MenuStringType extends eZDataType
{
	const DATA_TYPE_STRING = 'menustring';
	const MAX_LEN_FIELD = 'data_int1';
	const MAX_LEN_VARIABLE = '_menustring_max_string_length_';
	const DEFAULT_STRING_FIELD = 'data_text1';
	const DEFAULT_STRING_VARIABLE = '_menustring_default_value_';
    /*!
     Initializes with a string id and a description.
    */
    function MenuStringType()
    {
        $this->eZDataType( MenuStringType::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Menu line', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'text' ) ) );
        $this->MaxLenValidator = new eZIntegerValidator();
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
//             $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
//             $currentObjectAttribute = eZContentObjectAttribute::fetch( $contentObjectAttributeID,
//                                                                         $currentVersion );
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
        else
        {
            $contentClassAttribute = $contentObjectAttribute->contentClassAttribute();
            $default = $contentClassAttribute->attribute( "data_text1" );
            if ( $default !== "" )
            {
                $contentObjectAttribute->setAttribute( "data_text", $default );
            }
        }
    }

    /*!
     \reimp
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return $this->validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, false );
    }

    /*!
    */
    function validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, $isInformationCollector )
    {
        if ( $http->hasPostVariable( $base . '_menustring_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_menustring_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute = $contentObjectAttribute->contentClassAttribute();
            if ( $isInformationCollector == $classAttribute->attribute( 'is_information_collector' ) )
            {
                if ( $contentObjectAttribute->validateIsRequired() )
                {
                    if ( $data == "" )
                    {
                        $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                             'Input required.' ) );
                        return eZInputValidator::STATE_INVALID;
                    }
                }
            }
            $maxLen = $classAttribute->attribute( MenuStringType::MAX_LEN_FIELD );
            $textCodec = eZTextCodec::instance( false );
            if ( ($textCodec->strlen( $data ) <= $maxLen ) || ( $maxLen == 0 ) )
                return eZInputValidator::STATE_ACCEPTED;
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                 'The input text is too long. The maximum number of characters allowed is %1.' ),
                                                         $maxLen );
        }
        else
        {
            return eZInputValidator::STATE_ACCEPTED;
        }
        return eZInputValidator::STATE_INVALID;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_menustring_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_menustring_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $contentObjectAttribute->setAttribute( 'data_text', $data );
            return true;
        }
        return false;
    }

    /*!
     \reimp
    */
    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return $this->validateAttributeHTTPInput( $http, $base, $contentObjectAttribute, true );
    }

    /*!
     Fetches the http post variables for collected information
    */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        $dataText = $http->postVariable( $base . "_menustring_data_text_" . $contentObjectAttribute->attribute( "id" ) );

        $collectionAttribute->setAttribute( 'data_text', $dataText );

        return true;
    }

    /*!
     Does nothing since it uses the data_text field in the content object attribute.
     See fetchObjectAttributeHTTPInput for the actual storing.
    */
    function storeObjectAttribute( $attribute )
    {
    }

    /*!
     \reimp
     Simple string insertion is supported.
    */
    function isSimpleStringInsertionSupported()
    {
        return true;
    }

    /*!
     \reimp
     Inserts the string \a $string in the \c 'data_text' database field.
    */
    function insertSimpleString( $object, $objectVersion, $objectLanguage,
                                 $objectAttribute, $string,
                                 &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => true );
        $objectAttribute->setContent( $string );
        $objectAttribute->setAttribute( 'data_text', $string );
        return true;
    }

    function storeClassAttribute( $attribute, $version )
    {
    }

    function storeDefinedClassAttribute( $attribute )
    {
    }

    /*!
     \reimp
    */
    function validateClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $maxLenName = $base . MenuStringType::MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $maxLenValue = str_replace(" ", "", $maxLenValue );
            if( ( $maxLenValue == "" ) ||  ( $maxLenValue == 0 ) )
            {
                $maxLenValue = 0;
                $http->setPostVariable( $maxLenName, $maxLenValue );
                return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                $this->MaxLenValidator->setRange( 1, false );
                return $this->MaxLenValidator->validate( $maxLenValue );
            }
        }
        return eZInputValidator::STATE_INVALID;
    }

    /*!
     \reimp
    */
    function fixupClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $maxLenName = $base . MenuStringType::MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $this->MaxLenValidator->setRange( 1, false );
            $maxLenValue = $this->MaxLenValidator->fixup( $maxLenValue );
            $http->setPostVariable( $maxLenName, $maxLenValue );
        }
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $maxLenName = $base . MenuStringType::MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        $defaultValueName = $base . MenuStringType::DEFAULT_STRING_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $classAttribute->setAttribute( MenuStringType::MAX_LEN_FIELD, $maxLenValue );
        }
        if ( $http->hasPostVariable( $defaultValueName ) )
        {
            $defaultValueValue = $http->postVariable( $defaultValueName );

            $classAttribute->setAttribute( MenuStringType::DEFAULT_STRING_FIELD, $defaultValueValue );
        }
        return true;
    }
    function clean( $content, $type = "normal" )
    {
        
        $patterns[0] = '/<br>/';
        $patterns[1] = '/<dash-br>/';
        if ( $type == "normal" )
        {
            $replacements[0] = '';
            $replacements[1] = '';
        }
        if ( $type == "meta" )
        {
            $replacements[0] = '';
            $replacements[1] = '';
        }
        if ( $type == "menu" )
        {
            $replacements[0] = '<br />';
            $replacements[1] = '-<br />';
        }
        if ( $type == "xml" )
        {
            $replacements[0] = "\xc2\xad";
            $replacements[1] = "\xe2\x80\x8b";
        }
        return preg_replace( $patterns, $replacements, $content );
    }
    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $return = array( 'raw_data' => $contentObjectAttribute->attribute( 'data_text' ),
                      'menu_text' => MenuStringType::clean( $contentObjectAttribute->attribute( 'data_text' ), 'menu' ),
                      'text' => MenuStringType::clean( $contentObjectAttribute->attribute( 'data_text' ), 'normal' )
                      );
        return $return;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return MenuStringType::clean( $contentObjectAttribute->attribute( 'data_text' ) , "meta" );
    }

    /*!
     Returns the content of the string for use as a title
    */
    function title( $contentObjectAttribute, $name = null )
    {
        return MenuStringType::clean( $contentObjectAttribute->attribute( 'data_text' ) );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( MenuStringType::clean( $contentObjectAttribute->attribute( 'data_text' ) ) ) != '';
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     \reimp
    */
    function isInformationCollector()
    {
        return true;
    }

    /*!
     \reimp
    */
    function sortKey( $contentObjectAttribute )
    {
        include_once( 'lib/ezi18n/classes/ezchartransform.php' );
        $trans = eZCharTransform::instance();
        return $trans->transformByGroup( $contentObjectAttribute->attribute( 'data_text' ), 'lowercase' );
    }

    /*!
     \reimp
    */
    function sortKeyType()
    {
        return 'string';
    }

    /*!
     \reimp
    */
    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $maxLength = $classAttribute->attribute( self::MAX_LEN_FIELD );
        $defaultString = $classAttribute->attribute( self::DEFAULT_STRING_FIELD );
        $dom = $attributeParametersNode->ownerDocument;
        $maxLengthNode = $dom->createElement( 'max-length', $maxLength );
        $attributeParametersNode->appendChild( $maxLengthNode );
        if ( $defaultString )
        {
            $defaultStringNode = $dom->createElement( 'default-string', $defaultString );
        }
        else
        {
            $defaultStringNode = $dom->createElement( 'default-string' );
        }
        $attributeParametersNode->appendChild( $defaultStringNode );
    }

    /*!
     \reimp
    */
    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $maxLength = $attributeParametersNode->getElementsByTagName( 'max-length' )->item( 0 )->textContent;
        $defaultString = $attributeParametersNode->getElementsByTagName( 'default-string' )->item( 0 )->textContent;
        $classAttribute->setAttribute( self::MAX_LEN_FIELD, $maxLength );
        $classAttribute->setAttribute( self::DEFAULT_STRING_FIELD, $defaultString );
    }

    /// \privatesection
    /// The max len validator
    var $MaxLenValidator;
}

eZDataType::register( MenuStringType::DATA_TYPE_STRING, 'menustringtype' );

?>
