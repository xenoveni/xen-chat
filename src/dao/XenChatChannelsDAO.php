<?php

/**
 * Xen Chat channels DAO
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatChannelsDAO {
	
	/**
	* @var XenChatOptions
	*/
	private $options;
	
	public function __construct() {
		XenChatContainer::load('model/XenChatChannel');
		$this->options = XenChatOptions::getInstance();
	}

	/**
	 * Creates or updates the channel and returns it.
	 *
	 * @param XenChatChannel $channel
	 *
	 * @return XenChatChannel
	 * @throws Exception On validation error
	 */
	public function save($channel) {
		global $wpdb;

		// low-level validation:
		if ($channel->getName() === null) {
			throw new Exception('Name of the channel cannot equal null');
		}

		// prepare channel data:
		$table = XenChatInstaller::getChannelsTable();
		$columns = array(
			'name' => $channel->getName(),
			'password' => $channel->getPassword()
		);

		// update or insert:
		if ($channel->getId() !== null) {
			$wpdb->update($table, $columns, array('id' => $channel->getId()), '%s', '%d');
		} else {
			$wpdb->insert($table, $columns);
			$channel->setId($wpdb->insert_id);
		}

		return $channel;
	}

	/**
	 * Returns channel by ID.
	 *
	 * @param integer $id
	 *
	 * @return XenChatChannel|null
	 */
	public function get($id) {
		global $wpdb;

		$table = XenChatInstaller::getChannelsTable();
		$sql = sprintf('SELECT * FROM %s WHERE id = %d;', $table, intval($id));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateChannelData($results[0]);
		}

		return null;
	}

	/**
	 * Returns all channels sorted by name.
	 *
	 * @return XenChatChannel[]
	 */
	public function getAll() {
		global $wpdb;

		$channels = array();
		$table = XenChatInstaller::getChannelsTable();
		$sql = sprintf('SELECT * FROM %s ORDER BY name ASC;', $table);
		$results = $wpdb->get_results($sql);
		if (is_array($results)) {
			foreach ($results as $result) {
				$channels[] = $this->populateChannelData($result);
			}
		}

		return $channels;
	}

	/**
	 * Returns channel by name.
	 *
	 * @param string $name
	 *
	 * @return XenChatChannel|null
	 */
	public function getByName($name) {
		global $wpdb;

		$name = addslashes($name);
		$table = XenChatInstaller::getChannelsTable();
		$sql = sprintf('SELECT * FROM %s WHERE name = "%s";', $table, $name);
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateChannelData($results[0]);
		}

		return null;
	}

	/**
	 * Returns channels by names. The method is cached.
	 *
	 * @param string[] $names
	 *
	 * @return XenChatChannel[]
	 */
	public function getByNames($names) {
		global $wpdb;
		static $cache = array();

		$names = array_filter(array_map('addslashes', $names));
		if (count($names) === 0) {
			return array();
		}
		$namesCondition = implode("', '", $names);

		$cacheKey = md5($namesCondition);
		if (array_key_exists($cacheKey, $cache)) {
			return $cache[$cacheKey];
		}

		$table = XenChatInstaller::getChannelsTable();
		$sql = sprintf("SELECT * FROM %s WHERE name IN ('%s');", $table, $namesCondition);
		$results = $wpdb->get_results($sql);
		$channels = array();
		if (is_array($results)) {
			foreach ($results as $result) {
				$channels[] = $this->populateChannelData($result);
			}
		}

		$cache[$cacheKey] = $channels;

		return $channels;
	}

    /**
     * Deletes the channel by ID.
     *
     * @param integer $id
     *
     * @return null
     */
    public function deleteById($id) {
        global $wpdb;

        $id = intval($id);
        $table = XenChatInstaller::getChannelsTable();
        $wpdb->get_results(sprintf("DELETE FROM %s WHERE id = '%d';", $table, $id));
    }

	/**
	 * Converts raw object into XenChatChannel object.
	 *
	 * @param stdClass $rawChannelData
	 *
	 * @return XenChatChannel
	 */
	private function populateChannelData($rawChannelData) {
		$channel = new XenChatChannel();
		if ($rawChannelData->id > 0) {
			$channel->setId(intval($rawChannelData->id));
		}
		$channel->setName($rawChannelData->name);
		$channel->setPassword($rawChannelData->password);

		return $channel;
	}
}