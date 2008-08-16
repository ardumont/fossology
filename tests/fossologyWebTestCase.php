<?php

/***********************************************************
 Copyright (C) 2008 Hewlett-Packard Development Company, L.P.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 ***********************************************************/

/**
 * WebTest case for fossology
 *
 * This is the base class for fossology unit tests.  All tests should
 * require_once this class and then extend it.
 *
 * There are utility functions in this class for general use.
 *
 * This class defines where simpletest is and includes the modules
 * needed.
 *
 * @package fossology
 * @subpackage tests
 *
  * @version "$Id: $"
 *
 * Created on Jul 21, 2008
 */

//require_once('fossologyUnitTestCase.php');

if (!defined('SIMPLE_TEST'))
  define('SIMPLE_TEST', '/usr/share/php/simpletest/');

/* simpletest includes */
require_once SIMPLE_TEST . 'unit_tester.php';
require_once SIMPLE_TEST . 'reporter.php';
require_once SIMPLE_TEST . 'web_tester.php';
require_once ('../../../../tests/TestEnvironment.php');

global $URL;
global $USER;
global $PASSWORD;

/* does the path need to be modified?, I don't recommend running the
 * ../copy of the program to test.  I think the test should define/create
 * it when doing setup.
 */

class fossologyWebTestCase extends WebTestCase
{

  protected $url;
  protected $user;
  protected $password;

/*
  function __construct($url, $user, $password)
  {

    if (is_null($url))
    {
      $this->url = 'http://localhost/';
    } else
    {
      $this->url = $url;
    }
    if (is_null($user))
    {
      $this->user = 'fossy';
    } else
    {
      $this->user = $user;
    }
    if (is_null($password))
    {
      $this->password = 'fossy';
    } else
    {
      $this->password = $url;
    }
  }
  */

  public function repoLogin($browser = NULL, $user = 'fossy', $password = 'fossy')
  {
    global $URL;
    global $USER;
    global $PASSWORD;
    $page = NULL;
    $cookieValue = NULL;

    if (is_null($browser))
    {
      $browser = & new SimpleBrowser();
    }
    $host = $this->getHost($URL);
    $this->assertTrue(is_object($browser));
    $browser->useCookies();
    $cookieValue = $browser->getCookieValue($host, '/', 'Login');
    // need to check $cookieValue for validity
    $browser->setCookie('Login', $cookieValue, $host);
    $this->assertTrue($browser->get("$URL?mod=auth&nopopup=1"));
    $this->assertTrue($browser->setField('username', $user));
    $this->assertTrue($browser->setField('password', $password));
    $this->assertTrue($browser->isSubmit('Login'));
    $this->assertTrue($browser->clickSubmit('Login'));
    $page = $browser->getContent();
    preg_match('/User Logged In/', $page, $matches);
    $this->assertTrue($matches, "Login PASSED");
    $browser->setCookie('Login', $cookieValue, $host);
    $page = $browser->getContent();
    $NumMatches = preg_match('/User Logged Out/', $page, $matches);
    $this->assertFalse($NumMatches, "User Logged out!, Login Failed! %s");
    return($cookieValue);
  }

/**
 * function assertText
 *
 * @param string $page, a page of html or text to search
 * @param string $pattern a perl/php pattern e.g. '/suff/'
 *
 * @return boolean
 * @access public
 *
 */
  public function assertText($page, $pattern)
  {
    $NumMatches = preg_match($pattern, $page, $matches);
    //print "*** assertText: NumMatches is:$NumMatches\nmatches is:***\n";
    //$this->dump($matches);
    if($NumMatches)
    {
      return(TRUE);
    }
    return(FALSE);
  }

  /**
   * public function getHost
   *
   * returns the host (if present) from a URL
   *
   * @param string $URL a url in the form of http://somehost.xx.com/repo/
   *
   * @return string $host the somehost.xxx part is returned or
   *         NULL, if there is no host in the uri
   *
   */
  public function getHost($URL)
  {
    if(empty($URL))
    {
      return(NULL);
    }
    return(parse_url($URL, PHP_URL_HOST));      // can return NULL
  }

  /**
   * parse the folder id out of the html...
   *
   *@param string $folderName the name of the folder
   *@param string $page the xhtml page to search
   *
   *@return string (the folder id)
   */
  public function getFolderId($folderName, $page)
  {
    $found = preg_match("/.*value='([0-9].*?)'.*?;($folderName)<\//", $page, $matches);
    //print "DB: matches is:\n";
    //var_dump($matches) . "\n";
    return($matches[1]);
  }

/**
 * getBrowserUri get the url fragment to display the upload from the
 * xhtml page.
 *
 * @param string $name the name of a folder or upload
 * @param string $page the xhtml page to search
 *
 * @return $string the matching uri or null.
 *
 */
  public function getBrowseUri($name, $page)
  {
    //print "DB: GBURI: page is:\n$page\n";
    //$found = preg_match("/href='(.*?)'>($uploadName)<\/a>/", $page, $matches);
    // doesn't work: '$found = preg_match("/href='(.*?)'>$name/", $page, $matches);
    $found = preg_match("/href='((.*?)&show=detail).*?/", $page, $matches);
    //$found = preg_match("/ class=.*?href='(.*?)'>$name/", $page, $matches);
    print "DB: GBURI: found matches is:$found\n";
    print "DB: GBURI: matches is:\n";
    var_dump($matches) . "\n";
    if($found)
    {
      return($matches[1]);
    }
    else
    {
      return(NULL);
    }
  }
  /**
   * getNextLink given a pattern, find the link in the page and return
   * it.
   *
   * @param string $pattern a preg_match compatible pattern
   * @param string $page    the xhtml page to search
   *
   * @return string $result or null if no pattern found.
   *
   */
  public function getNextLink($pattern, $page, $debug=0)
  {
    $found = preg_match($pattern, $page, $matches);
    if ($debug)
    {
     print "DB: GNL: pattern is:$pattern\n";
     print "DB: GNL: found matches is:$found\n";
     print "DB: GNL: matches is:\n";
     var_dump($matches) . "\n";
    }
    if($found)
    {
      return($matches[1]);
    }
    else
    {
      return(NULL);
    }
  }

  /**
   * function makeUrl
   * Make a url from the host and query strings.
   *
   * @param $string $host the host (e.g. somehost.com, host.privatenet)
   * @param $string $query the query to append to the host.
   *
   * @return the http string or NULL on error
   */
  public function makeUrl($host, $query)
  {
    if(empty($host))
    {
      return(NULL);
    }
   if(empty($query))
    {
      return(NULL);
    }
    return("http://$host$query");
  }

  public function getUrl()
  {
    return $this->$url;
  }
  public function getUser()
  {
    return $this->$user;
  }
  public function getPassword()
  {
    return $this->$password;
  }
}
?>
