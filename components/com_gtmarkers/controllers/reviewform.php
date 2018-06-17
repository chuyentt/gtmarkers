<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gtmarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Review controller class.
 *
 * @since  1.6
 */
class GtmarkersControllerReviewForm extends JControllerForm
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gtmarkers.edit.review.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gtmarkers.edit.review.id', $editId);

		// Get the model.
		$model = $this->getModel('ReviewForm', 'GtmarkersModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_gtmarkers&view=reviewform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
            // Initialise variables.
            $app   = JFactory::getApplication();
            
            // Get the user data.
            $data = $app->input->get('jform', array(), 'array');

            // Check form ajax
            $ajax = $data['ajax'];
            
            if (!JSession::checkToken()) {
                if (!$ajax) {
                    // Check for request forgeries.
                    jexit(JText::_('JINVALID_TOKEN'));
                } else {
                    echo new JResponseJson($data, JText::_('JINVALID_TOKEN'), true);
                    $app->close();
                }
            }
            
            $model = $this->getModel('ReviewForm', 'GtmarkersModel');

            // Validate the posted data.
            $form = $model->getForm();

            if (!$form)
            {
                if (!$ajax) {
                    throw new Exception($model->getError(), 500);
                } else {
                    echo new JResponseJson($data, $model->getError(), true);
                    $app->close();
                }
            }

            // Validate the posted data.
            $data = $model->validate($form, $data);

            // Check for errors.
            if ($data === false)
            {
                // Get the validation messages.
                $errors = $model->getErrors();

                // Push up to three validation messages out to the user.
                for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
                {
                    if ($errors[$i] instanceof Exception)
                    {
                        $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                    }
                    else
                    {
                        $app->enqueueMessage($errors[$i], 'warning');
                    }
                }

                $input = $app->input;
                $jform = $input->get('jform', array(), 'ARRAY');

                // Save the data in the session.
                $app->setUserState('com_gtmarkers.edit.review.data', $jform);

                if (!$ajax) {
                    // Redirect back to the edit screen.
                    $id = (int) $app->getUserState('com_gtmarkers.edit.review.id');
                    $this->setRedirect(JRoute::_('index.php?option=com_gtmarkers&view=reviewform&layout=edit&id=' . $id, false));

                    $this->redirect();
                } else {
                    echo new JResponseJson($data, $model->getError(), true);
                    $app->close();
                }
            }

            // Attempt to save the data.
            $return = $model->save($data);

            // Check for errors.
            if ($return === false)
            {
                // Save the data in the session.
                $app->setUserState('com_gtmarkers.edit.review.data', $data);

                if (!$ajax) {
                    // Redirect back to the edit screen.
                    $id = (int) $app->getUserState('com_gtmarkers.edit.review.id');
                    $this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
                    $this->setRedirect(JRoute::_('index.php?option=com_gtmarkers&view=reviewform&layout=edit&id=' . $id, false));
                } else {
                    echo new JResponseJson($data, $model->getError(), true);
                    $app->close();
                }
            }

            // Check in the profile.
            if ($return)
            {
                $model->checkin($return);
            }

            // Clear the profile id from the session.
            $app->setUserState('com_gtmarkers.edit.review.id', null);

            if (!$ajax) {
                // Redirect to the list screen.
                $this->setMessage(JText::_('COM_GTMARKERS_ITEM_SAVED_SUCCESSFULLY'));
                $menu = JFactory::getApplication()->getMenu();
                $item = $menu->getActive();
                $url  = (empty($item->link) ? 'index.php?option=com_gtmarkers&view=reviews' : $item->link);
                $this->setRedirect(JRoute::_($url, false));

                // Flush the data from the session.
                $app->setUserState('com_gtmarkers.edit.review.data', null);
            } else {
                // Flush the data from the session.
                $app->setUserState('com_gtmarkers.edit.review.data', null);
                echo new JResponseJson(JText::_('COM_GTMARKERS_ITEM_SAVED_SUCCESSFULLY'));
                $app->close();
            }
	}

	/**
	 * Method to abort current operation
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		$app = JFactory::getApplication();

		// Get the current edit id.
		$editId = (int) $app->getUserState('com_gtmarkers.edit.review.id');

		// Get the model.
		$model = $this->getModel('ReviewForm', 'GtmarkersModel');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gtmarkers&view=reviews' : $item->link);
		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Method to remove data
	 *
	 * @return void
	 *
	 * @throws Exception
     *
     * @since 1.6
	 */
	public function remove()
    {
        $app   = JFactory::getApplication();
        $model = $this->getModel('ReviewForm', 'GtmarkersModel');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the profile
            $model->checkin($return);

            // Clear the profile id from the session.
            $app->setUserState('com_gtmarkers.edit.review.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gtmarkers&view=reviews' : $item->link);

            // Redirect to the list screen
            $this->setMessage(JText::_('COM_EXAMPLE_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(JRoute::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gtmarkers.edit.review.data', null);
        }
        catch (Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gtmarkers&view=reviews');
        }
    }
}
