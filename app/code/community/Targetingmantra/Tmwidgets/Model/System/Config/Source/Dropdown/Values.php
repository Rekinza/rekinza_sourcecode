<?php

class Targetingmantra_Tmwidgets_Model_System_Config_Source_Dropdown_Values
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'na',
                'label' => 'North America',
            ),
            array(
                'value' => 'asia',
                'label' => 'Asia',
            ),
            array(
                'value' => 'sa',
                'label' => 'South America',
            ),
        );
    }
}