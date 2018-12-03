<?php
/**
 * Movies controller.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/movies
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\MoviesModel;
use Form\MovieForm;

/**
 * Class MoviesController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class MoviesController implements ControllerProviderInterface
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
     * @return MoviesController Result
     */
    public function connect(Application $app)
    {
        $moviesController = $app['controllers_factory'];
        $moviesController->match('/add', array($this, 'addAction'))
            ->bind('movies_add');
        $moviesController->match('/add/', array($this, 'addAction'));
        $moviesController->match('/edit/{id}', array($this, 'editAction'))
            ->bind('movies_edit');
        $moviesController->match('/edit/{id}/', array($this, 'editAction'));
        $moviesController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('movies_delete');
        $moviesController->match('/delete/{id}/', array($this, 'deleteAction'));
        $moviesController->get('/', array($this, 'indexAction'));
        $moviesController->get('/index', array($this, 'indexAction'));
        $moviesController->get('/index/', array($this, 'indexAction'));
        $moviesController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('movies_index');
        return $moviesController;
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
			$moviesModel = new MoviesModel($app);
			$this->view = array_merge(
				$this->view,
				$moviesModel->getPaginatedMovies($page, $pageLimit)
			);
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Movies not found.'));
        }
        return $app['twig']->render('movies/index.twig', $this->view);
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
            $moviesModel = new MoviesModel($app);
            $this->view['movie'] = $moviesModel->getMovie($id);
        } catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Movie not found.'));
        }
        return $app['twig']->render('movies/view.twig', $this->view);
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
				->createBuilder(new MovieForm($app), $data)->getForm();
			$form->remove('id');
			$form->handleRequest($request);
		 
			if ($form->isValid()) {
				$data = $form->getData();
				$moviesModel = new MoviesModel($app);
				$moviesModel->saveMovie($data);
				$app['session']->getFlashBag()->add(
					'message',
					array(
					   'type' => 'success', 'content' => $app['translator']->trans('New movie added.')
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('movies_index'),
					301
				);
			}
			$this->view['form'] = $form->createView();
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('movies/add.twig', $this->view);
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
			$moviesModel = new MoviesModel($app);
			$id = (int) $request->get('id', 0);
			$movie = $moviesModel->getMovie($id);
			$this->view['movie'] = $movie;
			
			if (count($movie)) {
				$form = $app['form.factory']
				 ->createBuilder(new MovieForm($app), $movie)->getForm();
				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
					$moviesModel = new MoviesModel($app);
					$moviesModel->saveMovie($data);
					$app['session']->getFlashBag()->add(
						'message',
						array(
							'type' => 'success', 'content' => $app['translator']->trans('Movie edited.')
						)
					);
					return $app->redirect(
						$app['url_generator']->generate('movies_index'),
						301
					);
				}
				$this->view['form'] = $form->createView();
			} else {
				return $app->redirect(
					$app['url_generator']->generate('movies_add'),
					301
				);
			}
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('movies/edit.twig', $this->view);
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
			$moviesModel = new MoviesModel($app);
			$id = (int) $request->get('id', 0);
			$movie = $moviesModel->getMovie($id);
			$this->view['movie'] = $movie;

			if (count($movie)) {
				$form = $app['form.factory']
					->createBuilder(new MovieForm($app), $movie)->getForm();
				$form->remove('title');
				$form->remove('director_id');
				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
					$moviesModel = new MoviesModel($app);
					$moviesModel->deleteMovie($data['id']);
					$app['session']->getFlashBag()->add(
						'message',
						array(
							'type' => 'danger', 'content' => $app['translator']->trans('Movie deleted.')
						)
					);
					return $app->redirect(
						$app['url_generator']->generate('movies_index'),
						301
					);
				}

				$this->view['form'] = $form->createView();

			} else {
				return $app->redirect(
					$app['url_generator']->generate('movies_add'),
					301
				);
			}
		} catch (\PDOException $e) {
            $app->abort(404, $app['translator']->trans('Sorry, try again.'));
        }
        return $app['twig']->render('movies/delete.twig', $this->view);
    }
}
