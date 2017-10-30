<?php

namespace TheMasterNico\RepExtPHPBB\acp;

// Información sobre el modulo principal del sistema

/*
filename 	Fully name-spaced path to the Module class, starting with a leading slash.
title 	Language key for the module-title that is displayed in the ACP module management section.
modes 	Contains an array of modes for the module. See the next table for more details.
*/

class main_info
{
    public function module()
    {
        return array(
            'filename'  => '\TheMasterNico\RepExtPHPBB\acp\main_module',
            'title'     => 'ACP_DEMO_TITLE',// Es el titulo y funciona como "nombre de categoria"
            'modes'    => array(
                'settings'  => array( // Es como una "pestaña" en el modulo
                    'title' => 'ACP_DEMO',
                    'auth'  => 'ext_acme/demo && acl_a_board',
                    'cat'   => array('ACP_DEMO_TITLE'), // es la categoria a la que pertenece
                ),
            ),
        );
    }
}
