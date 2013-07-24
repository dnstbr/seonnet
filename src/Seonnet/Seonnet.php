<?php
namespace Seonnet;

use App;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Routing\Router as IlluminateRouter;

class Seonnet
{

  /**
   * The IoC Container
   *
   * @var Container
   */
  protected $app;

  /**
   * The Router
   *
   * @var Router
   */
  protected $router;

  /**
   * A cache of the matched routes
   *
   * @var array
   */
  protected $matchedRoutes = array();

  /**
   * Build a new Seonnet instance
   *
   * @param Container $app
   */
  public function __construct(Container $app, IlluminateRouter $router)
  {
    $this->app    = $app;
    $this->router = $router;
  }

  public function getRouter()
  {
    return $this->router;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// CORE METHODS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the page's title
   *
   * @param string $fallback A fallback title
   *
   * @return string
   */
  public function title($fallback = null)
  {
    $route = $this->getCurrentRoute();
    if(!$route) return $fallback;

    return $route->title;
  }

  /**
   * Get the current page's meta tags
   *
   * @return string
   */
  public function meta($route = null)
  {
    if (!$route) $route = $this->getCurrentRoute();
    if (!$route) return;

    return $route->metaTags;
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////////// ROUTES ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get a Route by it's URL/slug
   *
   * @param string $route
   *
   * @return Route
   */
  public function getRoute($route)
  {
    if (!$this->tableExists()) return;
      
    $url = $route->getPath();
    $action = $route->getAction();
    $route_name = $route->getParameter('_route');
    
    // Return Route in cache if any
    if (isset($this->matchedRoutes[$url])) {
      return $this->matchedRoutes[$url];
    }

    
    $routes = Route::all();

    // Search for a Route whose pattern matches the current URL
    foreach ($routes as $route) {
      if ((!empty($route->action) && $action == $route->action) or
          (!empty($route->name) && $route_name == $route->name) or
          ($route->pattern != "##" && preg_match($route->pattern, $url))) {

        $this->matchedRoutes[$url] = $route;

        return $route;
      }
    }
  }

  /**
   * Get the current Route
   *
   * @return Route
   */
  protected function getCurrentRoute()
  {
    return $this->getRoute(\Illuminate\Support\Facades\Route::getCurrentRoute());
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// HELPERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Check if the Seonnet table exists
   *
   * @return boolean
   */
  protected function tableExists()
  {
    $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

    return $schemaBuilder->hasTable('seonnet');
  }

}
