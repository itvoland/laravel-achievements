<?php
declare(strict_types=1);

namespace ITVOLAND\Achievements;

/**
 * Interface CanAchieve
 *
 * @package ITVOLAND\Achievements
 */
interface CanAchieve
{
    // Adds an specified amount of points of progress
    public function addProgressToAchiever($achiever, $points);

    // Sets the specified amount of points to this achiever
    public function setProgressToAchiever($achiever, $points);
}
