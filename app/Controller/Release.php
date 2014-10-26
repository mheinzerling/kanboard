<?php

namespace Controller;

/**
 * Release management
 *
 * @package controller
 * @author  Martin Heinzerling
 */
class Release extends Base
{
    /**
     * Get the release (common method between actions)
     *
     * @access private
     * @param $project_id
     * @return array
     */
    private function getRelease($project_id)
    {
        $release = $this->release->getById($this->request->getIntegerParam('release_id'));

        if (! $release) {
            $this->session->flashError(t('Release not found.'));
            $this->response->redirect('?controller=release&action=index&project_id='.$project_id);
        }

        return $release;
    }

    /**
     * List of releases for a given project
     *
     * @access public
     */
    public function index()
    {
        $project = $this->getProjectManagement();
        $this->response->html($this->projectLayout('release_index', array(
            'releases' => $this->release->getByProject($project['id'], false),
            'values' => array('project_id' => $project['id']),
            'errors' => array(),
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Releases')
        )));
    }

    /**
     * Validate and save a new release
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProjectManagement();

        $values = $this->request->getValues();

        list($valid, $errors) = $this->release->validateCreation($values);
        if ($valid) {

            if ($this->release->create($values)) {
                $this->session->flash(t('Your release have been created successfully.'));
                $this->response->redirect('?controller=release&action=index&project_id='.$project['id']);
            }
            else {
				var_dump($this->release->db->getLogMessages());
                $this->session->flashError(t('Unable to create your release.'));
            }
        }

        $this->response->html($this->projectLayout('release_index', array(
            'releases' => $this->release->getList($project['id'], false),
            'values' => $values,
            'errors' => $errors,
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Releases')
        )));
    }

    /**
     * Edit a release (display the form)
     *
     * @access public
     */
    public function edit()
    {
        $project = $this->getProjectManagement();
        $release = $this->getRelease($project['id']);

        $this->response->html($this->projectLayout('release_edit', array(
            'values' => $release,
            'errors' => array(),
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Releases')
        )));
    }

    /**
     * Edit a release (validate the form and update the database)
     *
     * @access public
     */
    public function update()
    {
        $project = $this->getProjectManagement();

        $values = $this->request->getValues();
        list($valid, $errors) = $this->release->validateModification($values);

        if ($valid) {

            if ($this->release->update($values)) {
                $this->session->flash(t('Your release have been updated successfully.'));
                $this->response->redirect('?controller=release&action=index&project_id='.$project['id']);
            }
            else {
                $this->session->flashError(t('Unable to update your release.'));
            }
        }

        $this->response->html($this->projectLayout('release_edit', array(
            'values' => $values,
            'errors' => $errors,
            'project' => $project,
            'menu' => 'projects',
            'title' => t('Releases')
        )));
    }

    /**
     * Confirmation dialog before removing a release
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProjectManagement();
        $release = $this->getRelease($project['id']);

        $this->response->html($this->projectLayout('release_remove', array(
            'project' => $project,
            'release' => $release,
            'menu' => 'projects',
            'title' => t('Remove a release')
        )));
    }

    /**
     * Remove a release
     *
     * @access public
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $project = $this->getProjectManagement();
        $release = $this->getRelease($project['id']);

        if ($this->release->remove($release['id'])) {
            $this->session->flash(t('Release removed successfully.'));
        } else {
            $this->session->flashError(t('Unable to remove this release.'));
        }

        $this->response->redirect('?controller=release&action=index&project_id='.$project['id']);
    }
}
