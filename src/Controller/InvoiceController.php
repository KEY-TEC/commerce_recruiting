<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\InvoiceManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * @var \Drupal\Core\Session\AccountProxy
   */
  private $accountProxy;

  /**
   * Constructs a new InvoiceController object.
   *
   * @param \Drupal\commerce_recruiting\InvoiceManagerInterface $invoice_manager
   *   The invoice service.
   */
  public function __construct(InvoiceManagerInterface $invoice_manager, AccountProxy $account_proxy) {
    $this->invoiceManager = $invoice_manager;
    $this->accountProxy = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruiting.invoice_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Decrypt recruiting url and redirect to product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function createInvoice(CampaignInterface $commerce_recruiting_campaign) {
    try {
      $user = User::load($this->accountProxy->id());
      $invoice = $this->invoiceManager->createInvoice($commerce_recruiting_campaign, $user);
      return new RedirectResponse($invoice->toUrl()->toString(), 302);;
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruiting')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Error while creating invoice. Please contact us."));
      return $this->redirect('<front>');
    }
  }

}
