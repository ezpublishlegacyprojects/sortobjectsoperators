<?php

/**
 * @license Code licensed under the GNU General Public License v2.0
 * @author 
 * @copyright (C) Alex Kozeka 2011
 */
class ContentObjectHelper {

    public static $sortConditions = null;
    public static $sortedObjects = null;

    public static function getObjectsFromNodes( $nodesOrObjects ) {
        $objects = array();

        foreach ( $nodesOrObjects as $nodeOrObject ) {
            $object = null;

            switch ( get_class( $nodeOrObject ) ) {
            case 'eZContentObjectTreeNode':
                $object = $nodeOrObject->object();
                break;
            
            case 'eZContentObject':
                $object = $nodeOrObject;
                break;

            default:
                die( 'Unsupported class!' );
            }
        
            $objects[] = $object;
        }

        return $objects;
    }

    public static function getUniqueObjects( $nodesOrObjects ) {
        $uniqueObjects = array();

        foreach ( $nodesOrObjects as $nodeOrObject ) {
            $object = null;

            switch ( get_class( $nodeOrObject ) ) {
            case 'eZContentObjectTreeNode':
                $object = $nodeOrObject->object();
                $objectId = $object->attribute( 'id' );
                break;
            
            case 'eZContentObject':
                $object = $nodeOrObject;
                $objectId = $object->attribute( 'id' );
                break;

            default:
                die( 'Unsupported class!' );
            }
        
            $uniqueObjects[$objectId] = $object;
        }

        return $uniqueObjects;
    }

    public static function getSortedObjects( $nodesOrObjects, $sortConditions = array() ) {
        self::$sortConditions = $sortConditions;
        
        self::$sortedObjects = self::getObjectsFromNodes( $nodesOrObjects );
    
        uksort( self::$sortedObjects, array( 'self', 'sortConditionsBasedCmpFunc' ) );

        return array_values( self::$sortedObjects );
    }

    public static function getUniqueSortedObjects( $nodesOrObjects, $sortConditions = array() ) {
        self::$sortConditions = $sortConditions;

        self::$sortedObjects = self::getUniqueObjects( $nodesOrObjects );

        uksort( self::$sortedObjects, array( 'self', 'sortConditionsBasedCmpFunc' ) );

        return array_values( self::$sortedObjects );
    }
//
    public static function sortConditionsBasedCmpFunc( $a, $b ) {
        foreach ( self::$sortConditions as $i => $sortCondition ) {
            $objectA = self::$sortedObjects[$a];
            $objectB = self::$sortedObjects[$b];

            $isSortAsc = $sortCondition[0];

            for ( $attributePathIdx = 1; $attributePathIdx < count( $sortCondition ); $attributePathIdx++ ) {
                $objectAAttributeValue = null;
                $objectBAttributeValue = null;

                $attributePathItem = $sortCondition[$attributePathIdx];
                if ( is_array( $attributePathItem ) ) {
                    list( $attributeType, $attributeName ) = $attributePathItem;
                } else {
                    $attributeType = $attributePathItem;
                }

                switch ( $attributeType ) {
                case 'system_attribute':
                    $objectAAttributeValue = self::getObjectSystemAttributeValue( $objectA, $attributeName );
                    $objectBAttributeValue = self::getObjectSystemAttributeValue( $objectB, $attributeName );
                    break;

                case 'attribute':
                    $objectAAttributeValue = self::getObjectAttributeValue( $objectA, $attributeName );
                    $objectBAttributeValue = self::getObjectAttributeValue( $objectB, $attributeName );
                    break;
                
                case 'relation_attribute':
                    $objectA = self::getRelatedObject( $objectA, $attributeName );
                    $objectB = self::getRelatedObject( $objectB, $attributeName );

                    $objectAAttributeValue = '';
                    $objectBAttributeValue = '';
                    if ( $objectA === null && $objectB === null ) {
                        // Jumping to comparing function
                        break 2;
                    }
                    
                    if ( $objectA === null ) {
                        // Making ObjectA to be less than ObjectB
                        // Jumping to comparing function
                        $objectBAttributeValue = 'a';
                        break 2;
                    }
                    if ( $objectB === null ) {
                        // Making ObjectA to be greater than ObjectB
                        // Jumping to comparing function
                        $objectAAttributeValue = 'a';
                        break 2;
                    }
                    break;

                case 'parent':
                    $objectA = $objectA->mainNode()->fetchParent()->object(); 
                    $objectB = $objectB->mainNode()->fetchParent()->object(); 
                    continue 2;
                
                default:
                    die( "Unknown attribute type: '{$attributeType}'!" );
                }
            }

            if ( $objectAAttributeValue === $objectBAttributeValue ) {
                // Jumping to next condition since objects are equal
                continue;
            }

            if ( $isSortAsc ) {
                return strcasecmp( $objectAAttributeValue, $objectBAttributeValue );
            } else {
                return -1 * strcasecmp( $objectAAttributeValue, $objectBAttributeValue );
            }
        }
        
        return 0;
    }    

    public static function getObjectAttribute( $object, $attributeName ) {
        $version = $object->currentVersion();
        $dataMap = $version->attribute( 'data_map' );
        return $dataMap[$attributeName];
    }

    public static function getObjectAttributeValue( $object, $attributeName ) {
        $attribute = self::getObjectAttribute( $object, $attributeName );
        return $attribute->toString();
    }

    public static function getObjectSystemAttributeValue( $object, $attributeName ) {
        return $object->attribute( $attributeName );
    }

    public static function getRelatedObject( $object, $attributeName, $params = false ) {
        $objectAttribute = self::getObjectAttribute( $object, $attributeName );
            
        $relatedObjects = $object->relatedContentObjectList(
            false,
            $object->attribute( 'id' ),
            $objectAttribute->attribute( 'contentclassattribute_id' ),
            false,
            $params
        );

        return ( count( $relatedObjects ) == 0 ) ? null : $relatedObjects[0];
    }

}

?>