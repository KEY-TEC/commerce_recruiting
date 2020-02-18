<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\InvoiceManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InvoiceController.
 */
class InvoiceController extends ControllerBase {

  /**
   * The invoice manager.
   *
   * @var \Drupal\commerce_recruiting\InvoiceManagerInterface
   */
  protected $invoiceManager;

  /**
   * Constructs a new InvoiceController object.
   *
   * @param \Drupal\commerce_recruiting\InvoiceManagerInterface $invoice_manager
   *   The invoice service.
   */
  public function __construct(InvoiceManagerInterface $invoice_manager) {
    $this->invoiceManager = $invoice_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruiting.invoice_manager')
    );
  }

  /**
   * Decrypt recruiting url and redirect to product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function createInvoice(CampaignInterface $campaign) {

    try {
      $invoice = $this->invoiceManager->createInvoice($campaign);
      return new RedirectResponse($invoice->toUrl(), 302);;
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruiting')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Error while creating invoice. Please contact us."));
      return $this->redirect('<front>');
    }
  }

}
