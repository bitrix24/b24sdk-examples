<?php

declare(strict_types=1);

namespace App;

use App\Application;

/**
 * Class Tasks
 *
 * This class represents a collection of tasks and provides methods to manage and retrieve tasks.
 * It includes functionality for filtering, sorting, and paginating tasks, as well as retrieving
 * user information from Bitrix24.
 *
 */
class Tasks {
    private $tasks = [];
    public $tasksPerPage = 3;
    public $sortField = 'id';
    public $sortOrder = true;

    /**
     * Retrieves the current user's information from Bitrix24.
     *
     * This method fetches the user's profile from the Bitrix24 service and extracts
     * the user's photo, first name, and the initial of their last name. If the user's
     * photo or name is not available, default values are used.
     *
     * @return array An associative array containing:
     *               - 'avatar': The URL of the user's photo.
     *               - 'fullName': The user's full name, consisting of their first name
     *                             and the initial of their last name.
     */
    public function getUserInfo(): array {

        // Obtain user info from Bitrix24
        $userProfile = Application::getB24Service()->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
        
        $userPhoto = isset($userProfile->PERSONAL_PHOTO) ? $userProfile->PERSONAL_PHOTO : 'https://whale-viable-wasp.ngrok-free.app/widgets/problem-tasks/assets/user.jpg';
        $userName = isset($userProfile->NAME) ? $userProfile->NAME : 'John';
        $lastNameInitial = isset($userProfile->LAST_NAME) ? mb_strtoupper(mb_substr($userProfile->LAST_NAME, 0, 1)) . '.' : 'D.';

        return [
            'avatar' => $userPhoto,
            'fullName' => trim($userName . ' ' . $lastNameInitial)
        ];
    }

    /**
     * Constructor for the Tasks class.
     *
     * This constructor initializes the tasks array with 15 tasks.
     * Each task is an associative array containing the following keys:
     * - 'id': The task ID, an integer from 1 to 15.
     * - 'title': The title of the task, in the format "Task X" where X is the task ID.
     * - 'deadline': The deadline for the task, set to X days from the current date.
     * - 'responsibleName': The name of the person responsible for the task, in the format "Responsible X" where X is the task ID.
     * - 'responsibleId': The ID of the person responsible for the task, an integer from 1 to 15.
     */
    public function __construct() {
        // Generate 15 tasks
        for ($i = 1; $i <= 15; $i++) {
            $this->tasks[] = [
                'id' => $i,
                'title' => "Task $i",
                'deadline' => date('Y-m-d', strtotime("+$i days")),
                'responsibleName' => "Responsible $i",
                'responsibleId' => $i,
            ];
        }
    }

    /**
     * Filters the tasks by their title.
     *
     * This method filters the tasks array and returns only those tasks
     * whose title contains the specified filter string.
     *
     * @param string $titleFilter The string to filter task titles by.
     * @return array The filtered array of tasks.
     */
    private function filterTasksByTitle($titleFilter) {
        return array_filter($this->tasks, function($task) use ($titleFilter) {
            return stripos($task['title'], $titleFilter) !== false;
        });
    }

    /**
     * Retrieves a paginated list of tasks with optional title filtering and sorting.
     *
     * @param int $pageIndex The page index for pagination (default is 1).
     * @param string $titleFilter A string to filter tasks by their title (default is an empty string).
     * @param string $sortField The field by which to sort the tasks (default is 'id').
     * @param bool $sortOrder The order of sorting: true for ascending, false for descending (default is true).
     * @return array A paginated and sorted list of tasks.
     */
    public function getTasks($pageIndex = 1, $titleFilter = '', $sortField = 'id', $sortOrder = true) {
        
        // Update sorting settings
        $this->sortField = $sortField;
        $this->sortOrder = $sortOrder;

        // Get filtered tasks
        $filteredTasks = $this->filterTasksByTitle($titleFilter);

        // Sort the result
        usort($filteredTasks, function($a, $b) {
            if ($a[$this->sortField] == $b[$this->sortField]) {
                return 0;
            }
            if ($this->sortOrder) { // true для asc
                return ($a[$this->sortField] < $b[$this->sortField]) ? -1 : 1;
            } else { // false для desc
                return ($a[$this->sortField] > $b[$this->sortField]) ? -1 : 1;
            }
        });

        // Pagination
        $tasksPerPage = 3;
        $offset = intval(($pageIndex - 1) * $tasksPerPage);
        return array_slice($filteredTasks, $offset, $tasksPerPage);
    }

    /**
     * Get the count of tasks that match the given title filter.
     *
     * @param string $titleFilter Optional. The title filter to apply to tasks. Default is an empty string.
     * @return int The count of tasks that match the title filter.
     */
    public function getTaskCount($titleFilter = '') {
        $filteredTasks = $this->filterTasksByTitle($titleFilter);

        return count($filteredTasks);
    }

}