<?php
/**
 * Omeka
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * @package Omeka\Controller
 */
class ElementSetsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('ElementSet');
    }
    
    protected function _getDeleteConfirmMessage($record)
    {
        return __('This will delete the element set and all elements assigned to '
             . 'the element set. Items will lose all metadata that is specific '
             . 'to this element set.');
    }
    /**
     * Can't add element sets via the admin interface, so disable these
     * actions from being POST'ed to.
     * 
     * @return void
     */
    public function addAction()
    {
        throw new Omeka_Controller_Exception_403();
    }
    
    public function editAction()
    {
        $elementSet = $this->_helper->db->findById();
        $db = $this->_helper->db;
        
        // Handle a submitted edit form.
        if ($this->getRequest()->isPost()) {
            
            // Delete existing element order to prevent duplicate indices.
            $db->getDb()->update(
                $db->getDb()->Element, 
                array('order' => null), 
                array('element_set_id = ?' => $this->getRequest()->getParam('id'))
            );
            
            // Update the elements.
            try {
                $elements = $this->getRequest()->getPost('elements');
                foreach ($elements as $id => $element) {
                    $elementRecord = $db->getTable('Element')->find($id);
                    if (ElementSet::ITEM_TYPE_NAME == $elementSet->name) {
                        if ($element['delete']) {
                            $elementRecord->delete();
                            continue;
                        }
                        $elementRecord->name = $element['name'];
                        $elementRecord->description = $element['description'];
                    } else {
                        $elementRecord->comment = trim($element['comment']);
                        $elementRecord->order = $element['order'];
                    }
                    $elementRecord->save();
                }
                $this->_helper->flashMessenger(__('The element set was successfully changed!'), 'success');
                $this->_helper->redirector('index');
            } catch (Omeka_Validate_Exception $e) {
                $this->_helper->flashMessenger($e);
            }
        }
        
        $this->view->element_set = $elementSet;
    }
    
    protected function _redirectAfterEdit($record)
    {
        $this->_helper->redirector('index');
    }
}
