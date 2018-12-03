<?php
/**
 * Seances controller.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/seances
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\SeancesModel;
use Model\DirectorsModel;
use Model\MoviesModel;
use Form\SeanceForm;
use Model\UsersModel;

/**
 * Class SeancesController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class SeancesController implements ControllerProviderInterface
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
     * @return SeancesController Result
     */
    public function connect(Application $app)
    {
        $seancesController = $app['controllers_factory'];
        $seancesController->match('/add', array($this, 'addAction'))
            ->bind('seances_add');
        $seancesController->match('/add/', array($this, 'addAction'));
        $seancesController->match('/edit/{id}', array($this, 'editAction'))
            ->bind('seances_edit');
        $seancesController->match('/edit/{id}/', array($this, 'editAction'));
        $seancesController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('seances_delete');
        $seancesController->match('/delete/{id}/', array($this, 'deleteAction'));
        $seancesController->get('/view/{id}', array($this, 'viewAction'))
            ->bind('seances_view');
        $seancesController->get('/view/{id}/', array($this, 'viewAction'));
        $seancesController->get('/contact', array($this, 'contactAction'))
            ->bind('seances_contact');
        $seancesController->get('/contact/', array($this, 'contactAction'));
        $seancesController->get('/index', array($this, 'indexAction'));
        $seancesController->get('/', array($this, 'indexAction'));
        $seancesController->get('/index/', array($this, 'indexAction'));
        $seancesController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('seances_index');
        return $seancesController;
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
            $seancesModel = new SeancesModel($app);
            $this->view = array_merge(
                $this->view,
                $seancesModel->getPaginatedSeances($page, $pageLimit)
            );
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Seances not found.'));
        }
        return $app['twig']->render('seances/index.twig', $this->view);
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
            $seancesModel = new SeancesModel($app);
            $this->view['seance'] = $seancesModel->getSeanceById($id);
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Seance not found.'));
        }
        return $app['twig']->render('seances/view.twig', $this->view);
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
			$data = array();

			$form = $app['form.factory']
				->createBuilder(new SeanceForm($app), $data)->getForm();
			$form->remove('id');
			$form->handleRequest($request);
		 
			if ($form->isValid()) {
				$data = $form->getData();
				$seancesModel = new SeancesModel($app);
				$seancesModel->saveSeance($data);
				$app['session']->getFlashBag()->add(
					'message',
					array(
					   'type' => 'success', 'content' => $app['translator']->trans('New seance added.')
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('seances_index'),
					301
				);
			}
			$this->view['form'] = $form->createView();
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('seances/add.twig', $this->view);
    }
    /**
     * Edit action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editAction(Application $app, Request $request)
    {
		try {
			$seancesModel = new SeancesModel($app);
			$id = (int) $request->get('id', 0);
			$seance = $seancesModel->getSeance($id);
			$this->view['seance'] = $seance;
			
			if (count($seance)) {
				$form = $app['form.factory']
				 ->createBuilder(new SeanceForm($app), $seance)->getForm();
				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
					$seancesModel = new SeancesModel($app);
					$seancesModel->saveSeance($data);
					$app['session']->getFlashBag()->add(
						'message',
						array(
							'type' => 'success', 'content' => $app['translator']->trans('Seance edited.')
						)
					);
					return $app->redirect(
						$app['url_generator']->generate('seances_index'),
						301
					);
				}
				$this->view['form'] = $form->createView();
			} else {
				return $app->redirect(
					$app['url_generator']->generate('seances_add'),
					301
				);
			}
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('seances/edit.twig', $this->view);
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
            $seancesModel = new SeancesModel($app);
            $id = (int) $request->get('id', 0);
            $seance = $seancesModel->getSeance($id);
            $this->view['seance'] = $seance;

            if (count($seance)) {
                $form = $app['form.factory']
                    ->createBuilder(new SeanceForm($app), $seance)->getForm();
                $form->remove('movie_id');
                $form->remove('director_id');
                $form->remove('location_id');
                $form->remove('hall');
                $form->remove('date');
                $form->remove('time');
                $form->remove('user_id');
                $form->remove('seats');
                $form->remove('price');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $seancesModel = new SeancesModel($app);
                    $seancesModel->deleteSeance($data['id']);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger', 'content' => $app['translator']->trans('Seance deleted.')
                        )
                    );
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
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('seances/delete.twig', $this->view);
    }
    /**
     * Contact action.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Output
     */
    public function contactAction(Application $app, Request $request)
    {
        return $app['twig']->render('seances/contact.twig', $this->view);
    }
}
