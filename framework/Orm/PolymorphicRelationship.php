<?php

declare(strict_types=1);

namespace Footup\Orm;

/*
|--------------------------------------------------------------------------
| PolymorphicRelationship — one model can belong to different types
|
| Example:
|   Comment belongs to either Post or Video (or any other model)
|   $comment->commentable()  → Post or Video
|   $post->comments()        → All comments for this Post
|   $video->comments()       → All comments for this Video
|
| Migration:
|   commentable_id INTEGER, commentable_type VARCHAR(255)
|   (e.g. commentable_id=5, commentable_type='Post')
|
| Usage:
|   $comment->morphTo('commentable')
|   $post->morphMany(Comment::class, 'commentable')
|--------------------------------------------------------------------------
*/

trait PolymorphicRelationship
{
    /**
     * Get the parent model for a polymorphic relationship
     * $comment->morphTo('commentable') → Post or Video
     */
    protected function morphTo(string $relationName): ?Model
    {
        $typeKey = $relationName . '_type';
        $idKey = $relationName . '_id';

        /** @var \Footup\Orm\Model $this */
        $modelType = $this->attributes[$typeKey] ?? null;
        /** @var \Footup\Orm\Model $this */
        $modelId = $this->attributes[$idKey] ?? null;

        if (!$modelType || !$modelId) {
            return null;
        }

        return $modelType::find($modelId);
    }

    /**
     * Get all related models of a polymorphic type
     * $post->morphMany(Comment::class, 'commentable')
     */
    protected function morphMany(string $relatedModel, string $relationName): array
    {
        $typeKey = $relationName . '_type';
        $idKey = $relationName . '_id';
        $myClass = static::class;
        /** @var \Footup\Orm\Model $this */
        $myId = $this->attributes['id'];

        return $relatedModel::where($typeKey, $myClass)
            ->where($idKey, $myId)
            ->get();
    }
}
