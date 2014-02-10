Wall Entries
============

Wall Entries are used to bind [Content Objects](content_objects.md) to a wall.

Each user or space is bound to a wall active record.

Wall Entry methods are automatically provided for each content object by SIActiveRecordContent class.

## Important Wall Methods by Content Class
* addToWall($wallId);
* getWallEntries()
* getWallEntryIds()

## Example to add a content items to a wall:

    $space = Space::model()->findByPk($spaceId);
    $wallId = $space->wall_id;
    $post->contentMeta->addToWall($wallId);
