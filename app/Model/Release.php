<?php

namespace Model;

use Core\Template;
use SimpleValidator\Validator;
use SimpleValidator\Validators;

/**
 * Project release model
 *
 * @package  model
 * @author   Martin Heinzerling<webmaster@mheinzerling.de>
 */
class Release extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const TABLE = 'project_releases';


	/**
	 * Create a release
	 *
	 * @access public
	 * @param  array    $values    Form values
	 * @return bool
	 */
	public function create(array $values)
	{
		$values['closed']=0;
		return $this->db->table(self::TABLE)->save($values);
	}

	/**
	 * Update a release
	 *
	 * @access public
	 * @param  array    $values    Form values
	 * @return bool
	 */
	public function update(array $values)
	{
		return $this->db->table(self::TABLE)->eq('id', $values['id'])->save($values);
	}

	/**
	 * Get a release by the id
	 *
	 * @access public
	 * @param  integer   $release_id    Release id
	 * @return array
	 */
	public function getById($release_id)
	{
		return $this->db->table(self::TABLE)->eq('id', $release_id)->findOne();
	}



	/**
	 * Return the list of all releases
	 *
	 * @access public
	 * @param  integer   $project_id    Project id
	 * @return array
	 */
	public function getByProject($project_id)
	{
		$all = $this->db->table(self::TABLE)
			->eq('project_id', $project_id)
			->asc('release_name')->findAll();
		return $all;
	}

	/**
	 * Validate release creation
	 *
	 * @access public
	 * @param  array   $values           Form values
	 * @return array   $valid, $errors   [0] = Success or not, [1] = List of errors
	 */
	public function validateCreation(array $values)
	{
		$rules = array(
			new Validators\Required('project_id', t('The project id is required')),
			new Validators\Required('name', t('The name is required')),
		);

		$v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

		return array(
			$v->execute(),
			$v->getErrors()
		);
	}

	/**
	 * Validate release modification
	 *
	 * @access public
	 * @param  array   $values           Form values
	 * @return array   $valid, $errors   [0] = Success or not, [1] = List of errors
	 */
	public function validateModification(array $values)
	{
		$rules = array(
			new Validators\Required('id', t('The id is required')),
			new Validators\Required('name', t('The name is required')),
		);

		$v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

		return array(
			$v->execute(),
			$v->getErrors()
		);
	}

	/**
	 * Common validation rules
	 *
	 * @access private
	 * @return array
	 */
	private function commonValidationRules()
	{
		return array(
			new Validators\Integer('id', t('The id must be an integer')),
			new Validators\Integer('project_id', t('The project id must be an integer')),
			new Validators\MaxLength('name', t('The maximum length is %d characters', 50), 50)
		);
	}

	/**
	 * Remove a release
	 *
	 * @access public
	 * @param  integer   $release_id    Release id
	 * @return bool
	 */
	public function remove($release_id)
	{
		$this->db->startTransaction();

		//TODO
		//$this->db->table(Task::TABLE)->eq('release_id', $release_id)->update(array('release_id' => 0));

		if (! $this->db->table(self::TABLE)->eq('id', $release_id)->remove()) {
			$this->db->cancelTransaction();
			return false;
		}

		$this->db->closeTransaction();

		return true;
	}
}
