<?php defined('SYSPATH') or die('No direct script access');

/**
 * Ushahidi OAuth:Client Console Commands
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Console
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Ushahidi_Console_OAuth_Client extends Ushahidi_Console_Oauth_Command {

	// todo: put me in a repo!
	public static function db_list($client = null)
	{
		$query = DB::select()
			->from('oauth_clients');

		if ($client)
		{
			$query->where('client_id', '=', $client);
		}

		return $query->execute()->as_array();
	}

	// todo: put me in a repo!
	public static function db_create(array $data)
	{
		$query = DB::insert('oauth_clients')
			->columns(array_keys($data))
			->values(array_values($data))
			;

		list($id, $count) = $query->execute();

		return $id;
	}

	// todo: put me in a repo!
	public static function db_delete($client)
	{
		$query = DB::delete('oauth_clients')
			->where('client_id', '=', $client);

		return $query->execute();
	}

	protected function configure()
	{
		$this
			->setName('oauth:client')
			->setDescription('List, create, and delete OAuth clients')
			->addArgument('action', InputArgument::OPTIONAL, 'list, create, or delete', 'list')
			->addOption('client', ['c'], InputOption::VALUE_OPTIONAL, 'client id')
			->addOption('secret', ['s'], InputOption::VALUE_OPTIONAL, 'secret key')
			->addOption('redirect', ['r'], InputOption::VALUE_OPTIONAL, 'redirect URI')
			;
	}

	protected function execute_list(InputInterface $input, OutputInterface $output)
	{
		$client = $input->getOption('client');
		return static::db_list($client);
	}

	protected function execute_create(InputInterface $input, OutputInterface $output)
	{
		$client = $input->getOption('client');
		if (!$client)
		{
			// We can't use the generic `get_client()` for **creation**,
			// because we need to verify that the user does **not** exist.
			$clients = Arr::pluck(Ushahidi_Console_OAuth_Client::db_list(), 'client_id');
			$ask = function($client) use ($clients)
			{
				if (in_array($client, $clients))
					throw new RuntimeException('Client "' . $client . '" already exists, try another name');

				return $client;
			};

			$client = $this->getHelperSet()->get('dialog')
				->askAndValidate($output, 'Enter name of new client: ', $ask, FALSE)
				;
		}

		$secret = $input->getOption('secret');
		$redirect = $input->getOption('redirect');

		if (!$secret)
			$secret = Text::random('distinct', 24);

		if (!$redirect)
			$redirect = '/';

		static::db_create([
			'client_id'     => $client,
			'client_secret' => $secret,
			'redirect_uri'  => $redirect,
			]);

		$input->setOption('client', $client);

		return $this->execute_list($input, $output);
	}

	protected function execute_delete(InputInterface $input, OutputInterface $output)
	{
		$client = $this->get_client($input, $output);

		if (static::db_delete($client))
			return "Deleted <info>{$client}</info>";

		// TODO: This should result in an error return (code 1) but would
		// require writing directly to output, rather than passing control back
		// to `Command::execute`.
		return "Client <error>{$client}</error> was not found";
	}
}
