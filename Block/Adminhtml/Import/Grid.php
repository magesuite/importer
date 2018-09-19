<?php

namespace MageSuite\Importer\Block\Adminhtml\Import;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_import';
        $this->_blockGroup = 'MageSuite_Importer';

        parent::_construct();

        $this->_headerText = __('Import Logs');
        $this->removeButton('add');
    }

}