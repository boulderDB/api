<?php

namespace App\Components\Entity;

use App\Entity\Tag;

interface TaggableInterface
{
    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag);

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag);

    /**
     * @return
     */
    public function getTags();
}