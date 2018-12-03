<?php
/**
 * Directors controller.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/directors
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\DirectorsModel;
use Model\UsersModel;
use Form\DirectorForm;

/**
 * Class DirectorsController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class DirectorsController implements ControllerProviderInterface
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
     * @return DirectorsController Result
     */
    public function connect(Application $app)
    {
        $directorsController = $app['controllers_factory'];
        $directorsController->match('/add', array($this, 'addAction'))
            ->bind('directors_add');
        $directorsController->match('/add/', array($this, 'addAction'));
        $directorsController->match('/edit/{id}', array($this, 'editAction'))
            ->bind('directors_edit');
        $directorsController->match('/edit/{id}/', array($this, 'editAction'));
        $directorsController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('directors_delete');
        $directorsController->match('/delete/{id}/', array($this, 'deleteAction'));
        $directorsController->get('/index', array($this, 'indexAction'));
        $directorsController->get('/', array($this, 'indexAction'));
        $directorsController->get('/index/', array($this, 'indexAction'));
        $directorsController->get('/{page}', array($this, 'indexAction'))
            ->value('page', 1)->bind('directors_index');
        return $directorsController;
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
            $directorsModel = new DirectorsModel($app);
            $this->view = array_merge(
                $this->view,
                $directorsModel->getPaginatedDirectors($page, $pageLimit)
            );
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Directors not found.'));
        }
        return $app['twig']->render('directors/index.twig', $this->view);
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
            $token = $app['security']->getToken();
            if (null !== $token) {
                $user = $token->getUser();
            }
            $form = $app['form.factory']
                ->createBuilder(new DirectorForm($app), $data)->getForm();
            $form->remove('id');
            $form->handleRequest($request);
         
            if ($form->isValid()) {
                $data = $form->getData();
                $directorsModel = new DirectorsModel($app);
                $directorsModel->saveDirector($data);
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                       'type' => 'success', 'content' => $app['translator']->trans('New director added.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('directors_index'),
                    301
                );
            }
            $this->view['form'] = $form->createView();
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error. Try later.'));
        }
        return $app['twig']->render('directors/add.twig', $this->view);
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
            $directorsModel = new DirectorsModel($app);
            $id = (int) $request->get('id', 0);
            $director = $directorsModel->getDirector($id);
            $this->view['director'] = $director;
            
            if (count($director)) {
                $form = $app['form.factory']
                 ->createBuilder(new DirectorForm($app), $director)->getForm();
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $directorsModel = new DirectorsModel($app);
                    $directorsModel->saveDirector($data);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success', 'content' => $app['translator']->trans('Director edited.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('directors_index'),
                        301
                    );
                }

                $this->view['form'] = $form->createView();

            } else {
                return $app->redirect(
                    $app['url_generator']->generate('directors_add'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error. Try later.'));
        }
        return $app['twig']->render('directors/edit.twig', $this->view);
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
            $directorsModel = new DirectorsModel($app);
            $id = (int) $request->get('id', 0);
            $director = $directorsModel->getDirector($id);
            $this->view['director'] = $director;

            if (count($director)) {
                $form = $app['form.factory']
                    ->createBuilder(new DirectorForm($app), $director)->getForm();
                $form->remove('firstName');
                $form->remove('surname');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    $directorsModel = new DirectorsModel($app);
                    $directorsModel->deleteDirector($data['id']);
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger', 'content' => $app['translator']->trans('Director deleted.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('directors_index'),
                        301
                    );
                }
                $this->view['form'] = $form->createView();
            } else {
                return $app->redirect(
                    $app['url_generator']->generate('directors_add'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error. Try later.'));
        }
        return $app['twig']->render('directors/delete.twig', $this->view);
    }
}
