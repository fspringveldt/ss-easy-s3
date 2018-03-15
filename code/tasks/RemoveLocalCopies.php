<?php
/**
 * Created by PhpStorm.
 * User: francospringveldt
 * Date: 2017/03/14
 * Time: 15:18 PM
 */

/**
 * Class RemoveLocalCopies
 *
 * This BuildTask can be cronned to remove all local copies of files marked for deletion
 *
 * @package
 */
class RemoveLocalCopies extends BuildTask
{
    protected $title = 'Removes local S3 files';
    protected $description = 'Remove local copies of files already migrated to S3';

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     */
    public function run($request)
    {
        /**
         * @var S3Facade $facade
         */
        $files = Image::get()
            ->filter(array('KeepLocal' => false));
        $numDeleted = 0;

        /**
         * @var Image $file
         */
        foreach ($files as $file) {
            $path = $file->getFullPath();
            unlink($path);
            $numDeleted++;
            do {
                $path = dirname($path);
            } while
            (
                !preg_match(
                    '/assets$/',
                    $path
                ) && Filesystem::remove_folder_if_empty($path));
        }
        echo "Total local files removed: $numDeleted";
    }
}
