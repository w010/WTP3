<?php


//use \TYPO3\CMS\Core\Utility\GeneralUtility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('w_tools').'Classes/Import.php');



class tx_wtools_import_crm extends tx_wtools_import {


	/**
	 * prepare and parse data from files
	 * get persons, groups, relations and companies
	 */
	protected function _prepareData()    {
		$success = false;

		foreach ($this->_pathFiles as $file)   {
			if (preg_match('/exportGroepen/', $file, $m2))  {
				//debugster($m2);
				// table name or just label. number to proper order in iteration - groups must be inserted before persons
				$this->data['1Groups'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['1Groups'])   $success = true;
				$this->counter['1Groups'] = 0;
			}

			if (preg_match('/exportGroepsdeelnemingen/', $file, $m3))   {
				$this->data['2GroupRelations'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['2GroupRelations'])   $success = true;
				$this->counter['2GroupRelations'] = 0;
			}

			if (preg_match('/exportBedrijven/', $file, $m1))    {
				$this->data['3Bedrijven'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['3Bedrijven'])   $success = true;
				$this->counter['3Bedrijven'] = 0;
			}

			if (preg_match('/exportPersonen/', $file, $m4)) {
				$this->data['4Personen'] = $this->XmlObj->getXmlFromFile( $this->_workingDir . $file );
				if ($this->data['4Personen'])   $success = true;
				$this->counter['4Personen'] = 0;
			}
		}
		// important - order by index, to force valid order of importing - first groups, persons last
		ksort($this->data);
		ksort($this->counter);
		return ['success' => $success];
	}



	/**
	 * save data from input files
	 * all files has same structure, so there's no need to split this method
	 * @return array result
	 */
	protected function _saveData()  {
		$success = true;
		// iterate record types, if more than one, ie. categories
		foreach($this->data as $recordType => $xmlItem)	{
			//debugster($recordType);
				$this->TTr->start('save set', 'save_'.$recordType, 'SAVE_GROUP');
			foreach($xmlItem->BusinessEntities[0]->BusinessEntity as $entity) {
				$this->_saveRecord($entity, $recordType);
				//				break;  // stop after first
			}
				$this->TTr->stop('save_'.$recordType, 'SAVE_GROUP');

			$this->log('already existed: ' . $recordType . ' - ' . count($this->alreadyExistsId[$recordType]));

			if ($this->counter[$recordType])
				$this->log('INSERTED items: ' . $recordType . ' - ' . $this->counter[$recordType]);

			if ($this->counter[$recordType.'_updated'])
				$this->log('UPDATED items: ' . $recordType . ' - ' . $this->counter[$recordType.'_updated']);

			// read groups again
			if ($recordType == '1Groups')   {
				$this->existingInDb['1Groups'] = $this->_getRecords('*', 'tt_address_group', 'AND pid = '.$this->extConf['pid']);
				$this->log('read Groups again from db after import: '.count($this->existingInDb['1Groups']));
			}
		}
//debugster( $this->data['test'] );
//debugster($this->counter);
//debugster($this->existingInDb);
//debugster($this->alreadyExists);
		return ['success' => $success, 'notice' => 'data saved'];
	}



	/**
	 * save crm record
	 *
	 * @param        $entity - array or simplexmlobject
	 * @param string $recordType
	 * @return array result
	 */
	protected function _saveRecord($entity, $recordType)	{
		switch ($recordType) {
			case '1Groups':
				// insert control
				if (false !== array_search((string) $entity->new_groupid, array_column($this->existingInDb[$recordType], 'description'))) {
					$this->alreadyExistsId[$recordType][] = (string) $entity->new_groupid;
					break;
				}
				$row = $this->_mapRow__tt_address_group($entity);
				// INSERT, if not found
				$result = $this->_insertItem($row, 'tt_address_group', $recordType);
				//debugster($row);
				break;

			case '2GroupRelations':
				// add to data array to later use for group relations
				$row = [
					'new_new_group_contactid' => (string) $entity->new_new_group_contactid,
					'new_groupid' => (string) $entity->new_groupid,
					'contactid' => (string) $entity->contactid
				];
				//debugster($row);
				$this->data['2GroupRelations_parsed'][] = $row;
				break;

			case '3Bedrijven':
				break;

			case '4Personen':
				//debugster($entity);
				// insert control
				$exists = false;
				if (false !== array_search((string) $entity->contactid, $this->existingInDb[$recordType])) {
					$this->alreadyExistsId[$recordType][] = (string) $entity->contactid;
					//break;
					$exists = true;
				}
				$row = $this->_mapRow__tt_address($entity);

				// test - this record has 2 relations: //if ($row['description'] == '82952189-bbe1-db11-810a-0014384ef846') {

				// prepare relations array
				$groupUids = is_array($row['__UNSET_groupUids']) ? $row['__UNSET_groupUids'] : [];
				array_unshift($groupUids, $this->extConf['categoryUid']);   // default as first
				//debugster($groupUids);

				// INSERT / UPDATE
				unset ($row['__UNSET_groupUids']);
				$result = $this->_insertItem($row, 'tt_address', $recordType, 'tt_address_group_mm', $groupUids, $exists ? $entity->contactid : 0);
				break;
		}

		//$this->log('insert: '.implode(',', $this->alreadyExists));
		return $result; //['notice' => 'insert: '.implode(',', $this->alreadyExists)];
	}


	/**
	 * maps xml structure to row array to insert into database
	 *
	 * @param SimpleXMLElement $entity
	 * @return array
	 */
	protected function _mapRow__tt_address(SimpleXMLElement &$entity)	{

		//debugster($entity);

		/*
		 * Find group relation for person
		 *
		 * There are such id fields in person: address1_addressid, address2_addressid, contactid
		 * and only contactid seems to relate to anything. rest are not found anywhere else, so we're not using them.
		 * There are such id fields in relation: new_new_group_contactid, new_groupid, contactid
		 * and new_new_group_contactid seems not be used for anything.
		 *
		 * Relation looks like this:
		 * Person.contactid <- Relation.contactid  =  Relation.new_groupid -> Group.new_groupid
		 */

		// use mm data to find and set group relation
		// find array index of relation, get the group id from it, finally get uid (collect all group uids)
		// $relationIndex = array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'contactid'));
		// find all indexes - there could be many group relations
		$relationIndexes = array_keys( array_column($this->data['2GroupRelations_parsed'], 'contactid'), (string) $entity->contactid );
		//debugster($relationIndexes);

		foreach ($relationIndexes as $relationIndex)  {
//		if (is_integer($relationIndex)) {
			// xml id!
			$groupid = $this->data['2GroupRelations_parsed'][$relationIndex]['new_groupid'];
			$groupIndex = array_search($groupid, array_column($this->existingInDb['1Groups'], 'description'));
			// group record
			// debugster($this->existingInDb['1Groups'][$groupIndex]);

			// typo sys uids!
			$groupUids[] = $this->existingInDb['1Groups'][$groupIndex]['uid'];
		}


		// relation test & find - take a look on last one
			/*
			$this->data['test']['_hits__address1_addressid__in__new_new_group_contactid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__address1_addressid__in__new_groupid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__address1_addressid__in__contactid'] += array_search((string) $entity->address1_addressid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__new_new_group_contactid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__new_groupid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__address2_addressid__in__contactid'] += array_search((string) $entity->address2_addressid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__new_new_group_contactid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'new_new_group_contactid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__new_groupid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'new_groupid')) ? 1 : 0;
			$this->data['test']['_hits__contactid__in__contactid'] += array_search((string) $entity->contactid, array_column($this->data['2GroupRelations_parsed'], 'contactid')) ? 1 : 0;*/
			//	debugster( $this->data['test'] );

		//		debugster($this->data['2GroupRelations_parsed']);

		return [
			// system
			'tstamp' => $this->scriptStartStamp,
			//'crdate' => strtotime($entity->createdon),    // doesn't have such
			'pid' => $this->extConf['pid'],
			'module_sys_dmail_html' => 1,
			'email' => (string) $entity->emailaddress1,
			'name' => (string) $entity->yomifullname,
			'first_name' => (string) $entity->new_voornaam,
			'last_name' => (string) $entity->lastname,
			'country' => (string) $entity->address1_country,
			'description' => (string) $entity->contactid,    // main id used from this field!
			'__UNSET_groupUids' => $groupUids   // only for setting mm in caller, unset before insert, there's no such field
			//'overall_official' => json_encode($entity->xpath('overall_official')),    // example
		];
	}

	/**
	 * maps xml structure to row array to insert into database
	 *
	 * @param SimpleXMLElement $entity
	 * @return array
	 */
	protected function _mapRow__tt_address_group(SimpleXMLElement &$entity)	{
		return [
			// system
			'tstamp' => $this->scriptStartStamp,
			'crdate' => (int) strtotime($entity->createdon),
			'pid' => $this->extConf['pid'],

			'title' => (string) $entity->new_name,
			'description' => (string) $entity->new_groupid     // MAIN ID used from this field!
		];
	}



	/**
	 * @param array  $row - item to insert into db
	 * @param string $table
	 * @param string $recordType
	 * @param string $mmTable
	 * @param array $mmUidsForeign - uids of foreign table, ie. categories (typo uids!)
	 * @param int $updateExistingId - id (possible not uid) of current record
	 * @return mixed
	 */
	protected function _insertItem($row, $table = 'tt_news', $recordType = 'default', $mmTable = 'tt_news_cat_mm', $mmUidsForeign = [], $updateExistingId = 0)	{

		// if using method with duplicate key update, omit this
		if (!$updateExistingId) {

			// neccessary to make valid query - only if manual build
			$row = self::db()->fullQuoteArray($row, $table, false);
			// query build
			$insert_fields = implode(',', array_keys($row));
			$insert_values = implode(',', $row);

			// $update_pairs = implode(',', $update_pairs_array);
			// If you specify ON DUPLICATE KEY UPDATE, and a row is inserted that would cause a duplicate value in a UNIQUE index or PRIMARY KEY, an UPDATE of the old row is performed.
			// INSERT INTO table (`uid`, `pid`) VALUES (1, 1) ON DUPLICATE KEY UPDATE uid = 1, pid = 1
			// $query = "INSERT INTO {$this->table} ({$insert_fields}) VALUES ({$insert_values}) ON DUPLICATE KEY UPDATE {$update_pairs}";
			$query = "INSERT INTO {$table} ({$insert_fields}) VALUES ({$insert_values})";

			//debugster($query);
			$result = self::db()->sql_query($query);
			if ($uidLocal = self::db()->sql_insert_id())
				$this->counter[$recordType]++;


			// insert mm relation records
			//debugster($GLOBALS['TYPO3_DB']->sql_insert_id());
			if (is_array($mmUidsForeign)  &&  $uidLocal)
				//foreach (explode(',', $mmUidsForeign) as $mmUidForeign)
				foreach ($mmUidsForeign as $mmUidForeign)
					if (intval($mmUidForeign)) {
						$this->_insertMmRelation($mmTable, $uidLocal, $mmUidForeign);
						$this->counter[$recordType]['mm'] ++;
					}
		}

		// update record
		else    {
			//debugster($row);

				// one update could take about 5-10 ms, so calculate this if import takes too long
				//$this->TTr->start('update_'.$updateExistingId, 'update_'.$updateExistingId);

			// cedris crm - finally, we use also this id as uniquality test on import. id is currently kept in description field
			$result = self::db()->exec_UPDATEquery($table, 'description = "'.$updateExistingId.'"', $row);
			$this->counter[$recordType.'_updated'] += intval($result);

				//$this->TTr->stop('update_'.$updateExistingId);

			//debugster($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
			//debugster($this->counter);
		}



		// clean up
		unset($row);
		unset($insert_fields);
		unset($insert_values);
		//unset($update_pairs);
		unset($query);



		// $this->TTr->stop();

		return $result;
	}






	/**
	 * read existing database data to prevent duplicates and set relations
	 */
	protected function _readExistingData()   {
		// get 1Groups - whole records would be helpful
		$this->existingInDb['1Groups'] = $this->_getRecords('*', 'tt_address_group', 'AND pid = '.intval($this->extConf['pid']));
		$this->log('LOAD current: total Group records in db: '.count($this->existingInDb['1Groups']));
		//debugster($this->existingInDb['1Groups']);

		// get 3Bedrijven

		// get 4Personen - only identifiers, which is kept in description field
		$this->existingInDb['4Personen'] = $this->_getExistingRecordsIds('description', 'tt_address', 'AND pid = '.$this->extConf['pid']);
		$this->log('LOAD current: total Person records in db: '.count($this->existingInDb['4Personen']));
	}


	/**
	 * note that this could possible not work with fe_users table (which doesn't have deleted, but disabled)
	 *
	 * @param        $fields
	 * @param        $table
	 * @param        $where
	 * @param string $enableFields
	 * @return array
	 */
	private function _getRecords($fields, $table, $where, $enableFields = ' AND NOT deleted AND NOT hidden') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$fields,
			$table,
			'1=1 '.$enableFields.' '.$where
		);
		$rows = [];
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		return $rows;
	}


	/**
	 * called by scheduler to display in backend task list
	 * @return string
	 */
	public function getAdditionalInformation()  {
		$this->init();
		$this->_prepareWorkingDirAndGetFiles();
		return 'CRM: files in directory: '.implode(', ', $this->_pathFiles);
	}

}


