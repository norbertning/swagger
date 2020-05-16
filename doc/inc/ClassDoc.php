<?php

/**
 * 类文档解析
 *
 * @author sijie.li
 * @date 2017.9.5
 */
class ClassDoc
{
    /**
     * @var ReflectionClass
     */
    private $class;

    private $name;

    private $author = '';

    private $version = '';

    private $title = '';

    private $description = '';

    public function __construct(ReflectionClass $class)
    {
        $this->class = $class;

        $this->name = $class->getName();
        $this->parse();
    }

    private function parse()
    {
        if (empty($this->class->getDocComment())) {
            return false;
        }
        // \R 可以匹配 \r,\n,\r\n 三种换行符
        $comments = preg_split('|\R|u', $this->class->getDocComment());
        if (isset($comments[1])) {
            $this->title = ltrim($comments[1], "\t *");
        }
        $descEnd = false;
        for ($i = 2; $i < count($comments); $i++) {
            $line = ltrim($comments[$i], "\t *");
            if (empty($line)) {
                continue;
            }
            if ($line[0] == '@') {
                $descEnd = true;
                $arr = explode(' ', substr($line, 1), 2);
                $var = $arr[0];
                $val = isset($arr[1]) ? trim($arr[1]) : '';
                switch ($var) {
                    case 'version':
                        $this->version = $val;
                        break;
                    case 'author':
                        $this->author = $val;
                        break;
                }
            } elseif (!$descEnd) {
                $this->description .= $line . "\n";
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}