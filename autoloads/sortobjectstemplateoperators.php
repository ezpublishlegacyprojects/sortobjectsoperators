<?php

/**
 * @license Code licensed under the GNU General Public License v2.0
 * @author 
 * @copyright (C) Alex Kozeka 2011
 */
class SortObjectsTemplateOperators {

    function __construct() {
    }

    function operatorList() {
        return array(
            'get_objects_from_nodes',
            'get_sorted_objects',
            'get_unique_sorted_objects',
        );
    }

    function namedParameterPerOperator() {
        return true;
    }

    function namedParameterList() {
        return array(
            'get_objects_from_nodes' => array(
                'nodes_or_objects' => array(
                    'type' => 'array',
                    'required' => true,
                ),
            ),
            'get_sorted_objects' => array(
                'nodes_or_objects' => array(
                    'type' => 'array',
                    'required' => true,
                ),
                'sort_conditions' => array(
                    'type' => 'array',
                    'required' => false,
                    'default' => array(),
                ),
            ),
            'get_unique_sorted_objects' => array(
                'nodes_or_objects' => array(
                    'type' => 'array',
                    'required' => true,
                ),
                'sort_conditions' => array(
                    'type' => 'array',
                    'required' => false,
                    'default' => array(),
                ),
            ),
        );
    }

    function modify(
        $tpl,
        $operatorName,
        $operatorParameters,
        $rootNamespace,
        $currentNamespace,
        &$operatorValue,
        $namedParameters
    ) {
        switch ( $operatorName ) {
        case 'get_objects_from_nodes':
            $operatorValue = ContentObjectHelper::getObjectsFromNodes(
                $namedParameters['nodes_or_objects']
            );
            break;
        
        case 'get_sorted_objects':
            $operatorValue = ContentObjectHelper::getSortedObjects(
                $namedParameters['nodes_or_objects'],
                $namedParameters['sort_conditions']
            );
            break;

        case 'get_unique_sorted_objects':
            $operatorValue = ContentObjectHelper::getUniqueSortedObjects(
                $namedParameters['nodes_or_objects'],
                $namedParameters['sort_conditions']
            );
            break;
        }
    }

}

?>