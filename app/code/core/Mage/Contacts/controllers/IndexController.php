<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Contacts
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Contacts index controller
 *
 * @category   Mage
 * @package    Mage_Contacts
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Contacts_IndexController extends Mage_Core_Controller_Front_Action
{

    const XML_PATH_EMAIL_RECIPIENT  = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'contacts/email/email_template';
    const XML_PATH_ENABLED          = 'contacts/contacts/enabled';

    public function preDispatch()
    {
        parent::preDispatch();

        if( !Mage::getStoreConfigFlag(self::XML_PATH_ENABLED) ) {
            $this->norouteAction();
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('contactForm')
            ->setFormAction( Mage::getUrl('*/*/post') );

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
/* trade */
 public function posttradeAction()
		 {
		$contact_us_email= Mage::getStoreConfig('contacts/email/recipient_email');
   
        $params = $this->getRequest()->getParams();
        $mail = new Zend_Mail();
		$first_name=$params['name'];
		$sur_name=$params['sur_name'];
		$mobile=$params['mobile'];
		$email=$params['email'];
		$business=$params['business'];
		$typebusiness=$params['typebusiness'];
		$region=$params['region'];
		$country=$params['country'];
		$comment=$params['comment'];
		$mail->setBodyHtml("Name: $first_name<br>
		Surname: $sur_name<br>
		Mobile: $mobile<br>
		Email: $email<br>
		Business: $business<br>
		Type of Business: $typebusiness<br>
		Region: $region<br>
		Country: $country<br>
		Comment: $comment<br>
		");
        $mail->setFrom($params['email'], $params['name']);
		$mail->addTo("$contact_us_email", 'Phdcleanse');
		$mail->setSubject("Affiliate Enquiry by $first_name");
        try {
            $mail->send();
			 Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
             rn;
			$this->_redirect('affiliate-enquiries/');
        }        
        catch(Exception $ex) 
		{
          Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
		   
			$this->_redirect('affiliate-enquiries/');
        }
 
        
    }
	/* Bridal page */
	public function postbridalAction()
		 {
		$contact_us_email= Mage::getStoreConfig('contacts/email/recipient_email');
   
        $params = $this->getRequest()->getParams();
        $mail = new Zend_Mail();
		$first_name=$params['name'];
		$sur_name=$params['sur_name'];
		$mobile=$params['mobile'];
		$email=$params['email'];
		$wedding_date=$params['datepicker1'];
		$how_many_party=$params['how_many_party'];
		$comment=$params['comment'];
		$mail->setBodyHtml("Name:$first_name<br>
		Surname-$sur_name<br>
		Mobile phone-$mobile<br>
		Email address-$email<br>
		Wedding date-$wedding_date<br>
		How many in the bridal party?-$how_many_party<br>
		Comments box-$comment<br>
		");
        $mail->setFrom($params['email'], $params['name']);
		$mail->addTo("$contact_us_email", 'Phdcleanse');
		$mail->setSubject("Bridal Enquiry by $first_name");
        try {
            $mail->send();
			 Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
             rn;
			$this->_redirect('cleanses/bridal-cleanse.html');
        }        
        catch(Exception $ex) 
		{
          Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
		   
			$this->_redirect('cleanses/bridal-cleanse.html');
        }
 
        
    }


}
