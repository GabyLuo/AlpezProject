<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * CORSMiddleware
 *
 * CORS checking
 */
class CORSMiddleware implements MiddlewareInterface
{    

    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $application
     *
     * @return bool
     */
    public function beforeHandleRoute(Event $event, Micro $application) {
        $application->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Authorization')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
 
            if ($application->request->isOptions()) {
                return true;
            }
            if ($application->request->getURI() === '/auth/login') {
                return true;
            }
            if ($application->request->getURI() === '/version') {
                return true;
            }
            if (strpos($application->request->getURI(), '/suppliers/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/suppliers/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/customers/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/products/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/movementPdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/movementPdfsi') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movement-details/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movement-details/file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/production-orders/packing-list/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/production-orders/handi-work/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/production-orders/cost/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'shippings/file/shipping/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'trip-expenses/file/expense/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'trip-expenses/file/expense/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'assets/shippings/files/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/assets') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/public/assets/purchase-orders/documents/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/public/assets/shopping-carts/ocs/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-documents/file/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/file1/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/file/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/file2/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/file3/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'assets/expense/files/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'incentives-production/csv/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'incentives-production/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shipments/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/getCSVFromShoppingCarts') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/getPdfFromShoppingCarts/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/sales-reports/getCSVFromShoppingCarts') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/sales-reports/getPdfFromShoppingCarts/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/sales-reports/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/kardex/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/kardex/csv/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-orders/pdf/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/createPdfFromRemission/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/inventory/') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/getPdfFromPayments') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/getCSVFromPayments') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/getCSVFromRemission') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), 'document-file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/customers/file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/products/file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shopping-carts/quotationNotePDF') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/ticket/crearTicket') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/documents/file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/getPdfFromAuxiliarPayments') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/getCSVFromAuxiliarPayments') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/close-sale/closeSales') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/close-sale/getCsvCloseSales') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-orders/getPdfFromPurchasePayments') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/documents/getfile') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/shipments/invoice-file') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/inventory-minimal-stock/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/inventory-minimal-stock/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/inventorybymark/pdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/movements/inventorybymark/csv') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-details/getPdfquotationNote') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/oldbalance/getPdf') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/oldbalance/getCSV') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/reports/getCsvpagbyclients') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/reports/getPdfpagbyclients') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-details/getReportShoppingToPDF') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-details/getReportShoppingToCSV') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-details/shoppingOfSuppliersPDF') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/purchase-order-details/getReportShoppingToCSVShoppingSupplier') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/reports/getPdfpagbySeller') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/invoices/quotationNotePDF') !== false) {
                return true;
            }
            if (strpos($application->request->getURI(), '/mail/generatePDF') !== false) {
                return true;
            }
            
            $isValid = Auth::validateRequest($application->request, $application->config->jwtkey);

            if (!$isValid) {
                $application->response
                ->setStatusCode(401, 'Unauthorized')
                ->setJsonContent([
                    'result' => true,
                    'message' => 'Access is not authorized'
                ])
                ->send();
                return false;
            }

        return true;
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @return bool
     */
    public function call(Micro $application) {
        return true;
    }
}