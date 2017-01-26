<?php

namespace epayment;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Femi
 */
interface IPayment {

    function cancelPayment($_sPaymentReference);

    function makePayment($_sPaymentReference,$_oCartList,$_sCurrency);

    // invoked by the remote gateway when a payment notifcation
    function onReceivePaymentNotification($_oNoficationArgs,&$_sErrorMessage);
}
?>
