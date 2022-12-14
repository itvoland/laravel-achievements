<p align="center">
    <img src="https://img001.prntscr.com/file/img001/raVRkHIpR5umm0BTyUtkpg.png" alt="Laravel Achievements Logo">
</p>

<p align="center">
<a href="https://app.travis-ci.com/itvoland/laravel-achievements"><img src="https://app.travis-ci.com/itvoland/laravel-achievements.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/itvoland/laravel-achievements"><img src="https://poser.pugx.org/itvoland/laravel-achievements/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/itvoland/laravel-achievements"><img src="https://poser.pugx.org/itvoland/laravel-achievements/license.svg" alt="License"></a>
</p>

An implementation of an Achievement System in Laravel, inspired by Laravel's Notification system.

## Table of Contents
1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Creating Achievements](#creating)
4. [Unlocking Achievements](#unlocking)
5. [Adding Progress](#progress)
6. [Retrieving Achievements](#retrieving)
7. [Event Listeners](#listening)
8. [License](#license)


## <a name="requirements"></a> Requirements

- Laravel 6 or higher
- PHP 7.2 or higher

## Versions and branches
- v2.* (master) - New namespace `ITVOLAND\*` with supporting Laravel >=6 and PHP7.2 (fixes, security and feature updates)

## <a name="installation"></a> Installation

Default installation is via [Composer](https://getcomposer.org/).

```bash
composer require itvoland/laravel-achievements
```

The service provider will automatically get registered. Or you could add the Service Provider manually to your
`config/app` file in the `providers` section.

```php
'providers' => [
    //...
    ITVOLAND\Achievements\AchievementsServiceProvider::class,
]
```

Backup your database and run the migrations in order to setup the required tables on the database.

```bash
php artisan migrate
```

## <a name="creating"></a> Creating Achievements
Similar to Laravel's implementation of [Notifications](https://laravel.com/docs/5.4/notifications), each Achievement is
represented by a single class (typically stored in the `app\Achievements` directory.) This directory will be created
automatically for you when you run the `make:achievement` command.

```bash
php artisan make:achievement UserMadeAPost
```
This command will put a fresh Achievement in your `app/Achievements` directory with only has two properties defined:
`name` and `description`. You should change the default values for these properties to something that better explains
what the Achievement is and how to unlock it. When you're done, it should look like this:

```php
<?php

namespace App\Achievements;

use ITVOLAND\Achievements\Achievement;

class UserMadeAPost extends Achievement
{
    /*
     * The achievement name
     */
    public $name = 'Post Created';

    /*
     * A small description for the achievement
     */
    public $description = 'Congratulations! You have made your first post!';
}
```

## <a name="unlocking"></a> Unlocking Achievements
Achievements can be unlocked by using the `Achiever` trait.

```php
<?php

namespace App;

use ITVOLAND\Achievements\Achiever;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Achiever;
}
```
This trait contains the `unlock` method, that can be used to unlock an Achievement. The `unlock` method expects an
`Achievement` instance:

```php
use App\Achievements\UserMadeAPost;

$user->unlock(new UserMadeAPost());
```
Remember that you're not restricted to the `User` class. You may add the `Achiever` trait to any entity that could
unlock Achievements.

## <a name="progress"></a> Adding Progress

Instead of directly unlocking an achievement, you can add a progress to it. For example, you may have an achievement
`UserMade10Posts` and you want to keep track of how the user is progressing on this Achievement.

In order to do that, you must set an additional parameter on your `UserMade10Posts` class, called `$points`:

```php
<?php

namespace App\Achievements;

use ITVOLAND\Achievements\Achievement;

class UserMade10Posts extends Achievement
{
    /*
     * The achievement name
     */
    public $name = '10 Posts Created';

    /*
     * A small description for the achievement
     */
    public $description = 'Wow! You have already created 10 posts!';

    /*
     * The amount of "points" this user need to obtain in order to complete this achievement
     */
    public $points = 10;
}
```
You may now control the progress by the methods `addProgress` and `removeProgress` on the `Achiever` trait.
Both methods expect an `Achievement` instance and an amount of points to add or remove:

```php
use App\Achievements\UserMade10Posts;

$user->addProgress(new UserMade10Posts(), 1); // Adds 1 point of progress to the UserMade10Posts achievement
```

In addition, you can use the methods `resetProgress` to set the progress back to 0 and `setProgress` to set it to a
specified amount of points:

```php
use App\Achievements\FiveConsecutiveSRanks;

if($rank != 'S'){
    $user->resetProgress(new FiveConsecutiveSRanks());
} else {
    $user->addProgress(new FiveConsecutiveSRanks(), 1);
}
```

```php
use App\Achievements\Have1000GoldOnTheBag;

$user->setProgress(new Have100GoldOnTheBag(), $user->amountOfGoldOnTheBag);
```

Once an Achievement reach the defined amount of points, it will be automatically unlocked.

## <a name="retrieving"></a> Retrieving Achievements
The `Achiever` trait also adds a convenient relationship to the entity implementing it: `achievements()`. You can use it
to retrieve progress for all achievements the entity has interacted with. Since `achievements()` is a relationship, you
can use it as a QueryBuilder to filter data even further.

```php
$achievements   = $user->achievements;
$unlocked_today = $user->achievements()->where('unlocked_at', '>=', Carbon::yesterday())->get();
```

You can also search for a specific achievement using the `achievementStatus()` method.

```php
$details = $user->achievementStatus(new UserMade10Posts());
```

There are also three additional helpers on the `Achiever` trait: `lockedAchievements()`, `inProgressAchievements()` and `unlockedAchievements()`.

## <a name="listening"></a> Event Listeners

### Listening to all Achievements
Laravel Achievements provides two events that can be listened to in order to provide "Achievement Unlocked" messages or similar. Both events receive the instance of `AchievementProgress` that triggered them.

The `ITVOLAND\Achievements\Event\Progress` event triggers whenever an Achiever makes progress, but doesn't unlock an Achievement. The `ITVOLAND\Achievements\Event\Unlocked` event triggers whenever an Achiever actually unlocks an achievement.

Details on how to listen to those events are explained on [Laravel's Event documentation](https://laravel.com/docs/5.3/events).

### Listening to specific Achievements

The event listeners mentioned above triggers for all Achievements. If you would like to add an event listener for only a specific Achievement, you can do so by implementing the methods `whenUnlocked` or `whenProgress` on the `Achievement` class.

```php
<?php

namespace App\Achievements;

use ITVOLAND\Achievements\Achievement;

class UserMade50Posts extends Achievement
{
    /*
     * The achievement name
     */
    public $name = '50 Posts Created';

    /*
     * A small description for the achievement
     */
    public $description = 'Wow! You have already created 50 posts!';

    /*
     * The amount of "points" this user need to obtain in order to complete this achievement
     */
    public $points = 50;

    /*
     * Triggers whenever an Achiever makes progress on this achievement
    */
    public function whenProgress($progress)
    {

    }

    /*
     * Triggers whenever an Achiever unlocks this achievement
    */
    public function whenUnlocked($progress)
    {

    }
}
```
## <a name="license"></a> License

Laravel Achievements is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
