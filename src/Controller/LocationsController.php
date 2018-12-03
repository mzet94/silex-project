<?php
/**
 * Locations controller.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/locations
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\LocationsModel;
use Form\LocationForm;

/**
 * Class LocationsController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class LocationsController implements ControllerProviderInterface
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
     * @return LocationsController Result
     */
    public function connect(Application $app)
    {
        $locationsController = $app['controllers_factory'];
        $locationsController->match('/add', array($this, 'addAction'))
            ->bind('locations_add');
        $locationsController->match('/add/', array($this, 'addAction'));
        $locationsController->get('/index', array($this, 'indexAction'));
        $locationsController->get('/', array($this, 'indexAction'));
        $locationsController->get('/index/', array($this, 'indexAction'));
        $locationsController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('locations_index');
        return $locationsController;
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
            $locationsModel = new LocationsModel($app);
            $this->view = array_merge(
                $this->view,
                $locationsModel->getPaginatedLocations($page, $pageLimit)
            );
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error. Try later.'));
        }
        return $app['twig']->render('locations/index.twig', $this->view);
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
                ->createBuilder(new LocationForm($app), $data)->getForm();
            $form->remove('id');
            $form->handleRequest($request);
         
            if ($form->isValid()) {
                $data = $form->getData();
                $locationsModel = new LocationsModel($app);
                $locationsModel->saveLocation($data);
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                       'type' => 'success', 'content' => $app['translator']->trans('New location added.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('locations_index'),
                    301
                );
            }
            $this->view['form'] = $form->createView();
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Error. Try later.'));
        }
        return $app['twig']->render('locations/add.twig', $this->view);
    }
}
