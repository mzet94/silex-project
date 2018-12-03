<?php
/**
 * Transactions controller.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/transactions
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\TransactionsModel;
use Form\TransactionForm;
use Model\UsersModel;
use Model\SeancesModel;

/**
 * Class TransactionsController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class TransactionsController implements ControllerProviderInterface
{

    /**
     * Data for view.
     *
     * @access protected
     * @var array $view
     */
    protected $view = array();

    /**
     * Routing settings.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @return TransactionsController Result
     */
    public function connect(Application $app)
    {
        $transactionsController = $app['controllers_factory'];
        $transactionsController->match('/add', array($this, 'addAction'))
            ->bind('transactions_add');
        $transactionsController->match('/add/', array($this, 'addAction'));
        $transactionsController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('transactions_delete');
        $transactionsController->match('/delete/{id}/', array($this, 'deleteAction'));
        $transactionsController->get('/view/{id}', array($this, 'viewAction'))
            ->bind('transactions_view');
        $transactionsController->get('/view/{id}/', array($this, 'viewAction'));
        $transactionsController->get('/indexorders', array($this, 'indexOrdersAction'));
        $transactionsController->get('/indexorders/{page}', array($this, 'indexOrdersAction'))
                        ->value('page', 1)->bind('index_orders');
        $transactionsController->get('/', array($this, 'indexAction'));
        $transactionsController->get('/index', array($this, 'indexAction'));
        $transactionsController->get('/index/', array($this, 'indexAction'));
        $transactionsController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('transactions_index');
        return $transactionsController;
    }
    /**
     * Index action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function indexAction(Application $app, Request $request)
    {
        try {
            $pageLimit = 5;
            $page = (int) $request->get('page', 1);
            $transactionsModel = new TransactionsModel($app);
            $this->view = array_merge(
                $this->view,
                $transactionsModel->getPaginatedTransactions($page, $pageLimit)
            );
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Transactions not found.'));
        }
        return $app['twig']->render('transactions/index.twig', $this->view);
    }
    /**
     * Index orders action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function indexOrdersAction(Application $app, Request $request) 
    {
        try {
            $transaction_id = (int)$request->get('id', null);
            $token = $app['security']->getToken();
			if (null !== $token) {
				$username = $token->getUser()->getUsername();
			}
            $usersModel = new UsersModel($app);
            $user = $usersModel->getUserByLogin($username);
            $id = $user['id'];
            $pageLimit = 5;
            $page = (int) $request->get('page', 1);
            $transactionsModel = new TransactionsModel($app);
            $transaction = $transactionsModel->getTransaction($transaction_id);
            $this->view = array_merge(
                $this->view,
                $transactionsModel->getPaginatedOrders($page, $pageLimit, $id)
            );
        } catch (\PDOException $e) {
           $app->abort(404, $app['translator']->trans('Transactions not found.'));
        }
            return $app['twig']->render('transactions/indexorders.twig', $this->view);
    }

    /**
     * View action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function viewAction(Application $app, Request $request)
    {
        try {
            $id = (int)$request->get('id', null);
			$token = $app['security']->getToken();
			if (null !== $token) {
				$username = $token->getUser()->getUsername();
			}
			$usersModel = new UsersModel($app);
			$user = $usersModel->getUserByLogin($username);
			$this->view['user'] = $user;
            $transactionsModel = new TransactionsModel($app);
            $this->view['transaction'] = $transactionsModel->getTransaction($id);
        } catch (\PDOException $e) {
			$app->abort(404, $app['translator']->trans('Transaction not found'));
        }
        return $app['twig']->render('transactions/view.twig', $this->view);
    }
    /**
     * Add action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function addAction(Application $app, Request $request)
    {
        try {
			$seancesModel = new SeancesModel($app);
			$id = (int) $request->get('id', 0);
            $data = array();
			
            $seance = $seancesModel->getSeance($id);
            $this->view['seance'] = $seance;
			
            $token = $app['security']->getToken();
            if (null !== $token) {
                $username = $token->getUser()->getUsername();
            }
            $usersModel = new UsersModel($app);
            $userid = $usersModel->getUserId($username);
            $data['user_id'] = $userid;
			
            $date = date('Y-m-d');
            $data['date'] = $date;
            $paymentStatus = '2';
            $data['paymentStatus_id'] = $paymentStatus;
            
            if (count($seance)) {
                $form = $app['form.factory']
                    ->createBuilder(new TransactionForm($app), $data)->getForm();
                $form->remove('id');
                $form->remove('date');
                $form->remove('user_id');
                $form->remove('paymentStatus_id');
                $form->remove('transactionDetails_id');
                $form->remove('transaction_id');
                $form->remove('seance_id');
                $form->remove('ticketsPrice');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $transactionsModel = new TransactionsModel($app);
                    $transactionid = $transactionsModel->saveTransaction($data);
                    if (count($transactionid)) {
                        $details = array(
                            'transaction_id' => (int) $transactionid,
                            'seance_id' => (int) $seance['id'],
                        );
                        $seancesModel = new SeancesModel($app);
                        $transactionsModel = new TransactionsModel($app);
                        $ticketprice = $seancesModel->getSeance($details['seance_id']);
                        $cost = $transactionsModel->countPrice(
                            $data['tickets'],
                            $ticketprice['price']
                        );
                        $details['ticketsPrice'] = $cost;
                        $transactionsModel = new TransactionsModel($app);
                        $transactionsModel->saveTransactionDetails($details);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                            'type' => 'success', 'content' => $app['translator']
                                    ->trans('Transaction completed successfully.')
                            )
                        );
                    } else {
						$app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'danger',
                                'content' => $app['translator']
                                    ->trans('Transaction failed.')
                            )
                        );
					}
                    return $app->redirect(
                        $app['url_generator']->generate('seances_index'),
                        301
                    );
                }
                $this->view['form'] = $form->createView();
			} else {
                return $app->redirect(
                    $app['url_generator']->generate('seances_index'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error.'));
        }
        return $app['twig']->render('transactions/add.twig', $this->view);
    }
        
    /**
     * Delete action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteAction(Application $app, Request $request)
    {
        try {
            $transactionsModel = new TransactionsModel($app);
            $id = (int) $request->get('id', 0);
            $transaction = $transactionsModel->getTransaction($id);
            $this->view['transaction'] = $transaction;

            if (count($transaction)) {
                $form = $app['form.factory']
                    ->createBuilder(new TransactionForm($app), $transaction)->getForm();
                $form->remove('user_id');
                $form->remove('date');
                $form->remove('tickets');
                $form->remove('paymentMethod_id');
                $form->remove('paymentStatus_id');
                $form->remove('collection_id');
                $form->remove('transactionDetails_id');
                $form->remove('transaction_id');
                $form->remove('seance_id');
                $form->remove('ticketsPrice');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $transactionsModel = new TransactionsModel($app);
                    $transactionsModel->deleteTransaction($data['id']);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger', 'content' => $app['translator']
                                ->trans('Transaction deleted.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('transactions_index'),
                        301
                    );
                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('transactions_index'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error.'));
        }
        return $app['twig']->render('transactions/delete.twig', $this->view);
    }
}
