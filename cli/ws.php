<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/update_cron.php
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/ws/app.php';

// Your shell script
use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;

class JoomlaSession implements SessionHandlerInterface{
     /**
     * Open session.
     *
     * @see http://php.net/sessionhandlerinterface.open
     *
     * @param string $savePath    Save path.
     * @param string $sessionName Session Name.
     *
     * @throws \RuntimeException If something goes wrong starting the session.
     *
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * Close session.
     *
     * @see http://php.net/sessionhandlerinterface.close
     *
     * @return boolean
     */
    public function close() {
        return true;
    }

    /**
     * Read session.
     *
     * @param string $sessionId
     *
     * @see http://php.net/sessionhandlerinterface.read
     *
     * @throws \RuntimeException On fatal error but not "record not found".
     *
     * @return string String as stored in persistent storage or empty string in all other cases.
     */
    public function read($sessionId) {

        // Get the database connection object and verify its connected.
        $db = JFactory::getDbo();

        try
        {
            // Get the session data from the database table.
            $query = $db->getQuery(true)
                ->select($db->quoteName('data'))
            ->from($db->quoteName('#__session'))
            ->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

            $db->setQuery($query);

            $result = (string) $db->loadResult();

            $result = str_replace('\0\0\0', chr(0) . '*' . chr(0), $result);

            return $result;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * Commit session to storage.
     *
     * @see http://php.net/sessionhandlerinterface.write
     *
     * @param string $sessionId Session ID.
     * @param string $data      Session serialized data to save.
     *
     * @return boolean
     */
    public function write($sessionId, $data) {
        // Get the database connection object and verify its connected.
        $db = JFactory::getDbo();

        $data = str_replace(chr(0) . '*' . chr(0), '\0\0\0', $data);

        try
        {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__session'))
                ->set($db->quoteName('data') . ' = ' . $db->quote($data))
                ->set($db->quoteName('time') . ' = ' . $db->quote((int) time()))
                ->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

            // Try to update the session data in the database table.
            $db->setQuery($query);
            if (!$db->execute())
            {
                return false;
            }
            /* Since $db->execute did not throw an exception, so the query was successful.
            Either the data changed, or the data was identical.
            In either case we are done.
            */
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * Destroys this session.
     *
     * @see http://php.net/sessionhandlerinterface.destroy
     *
     * @param string $sessionId Session ID.
     *
     * @throws \RuntimeException On fatal error.
     *
     * @return boolean
     */
    public function destroy($sessionId) {
        // Get the database connection object and verify its connected.
        $db = JFactory::getDbo();

        try
        {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__session'))
                ->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

            // Remove a session from the database.
            $db->setQuery($query);

            return (boolean) $db->execute();
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * Garbage collection for storage.
     *
     * @see http://php.net/sessionhandlerinterface.gc
     *
     * @param integer $lifetime Max lifetime in seconds to keep sessions stored.
     *
     * @throws \RuntimeException On fatal error.
     *
     * @return boolean
     */
    public function gc($lifetime) {
        // Get the database connection object and verify its connected.
        $db = JFactory::getDbo();

        // Determine the timestamp threshold with which to purge old sessions.
        $past = time() - $lifetime;

        try
        {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__session'))
                ->where($db->quoteName('time') . ' < ' . $db->quote((int) $past));

            // Remove expired sessions from the database.
            $db->setQuery($query);

            return (boolean) $db->execute();
        }
        catch (Exception $e)
        {
            return false;
        }
    }
}

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @package  Joomla.Cli
 * @since    2.5
 */
class Ws extends JApplicationCli
{
	public function isAdmin() {
		return true;
	}
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{ 	
		// Loop
    	$loop   = React\EventLoop\Factory::create();
    	$this->wsapp =  new WsApp($this, $loop);

    	$session = new SessionProvider(
	        $this->wsapp
	      , new JoomlaSession()
	    );

    	// Set up our WebSocket server for clients wanting real-time updates
	    $webSock = new React\Socket\Server($loop);
	    $webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
	    $webServer = new Ratchet\Server\IoServer(
	        new Ratchet\Http\HttpServer(
	            new Ratchet\WebSocket\WsServer(
	                $session
	            )
	        ),
	        $webSock
	    );

	    $loop->run();
	}
}

JApplicationCli::getInstance('Ws')->execute();
