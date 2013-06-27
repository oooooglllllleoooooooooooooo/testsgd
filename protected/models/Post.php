<?php

/**
 * This is the model class for table "{{post}}".
 *
 * The followings are the available columns in table '{{post}}':
 * @property integer $id
 * @property string $title
 * @property integer $begin_time
 * @property integer $end_time
 * @property string $location
 * @property string $category
 * @property string $content
 * @property string $tags
 * @property string $url
 * @property integer $status
 * @property string $cost
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $author_id
 *
 * The followings are the available model relations:
 * @property Comment[] $comments
 * @property User $author
 */
class Post extends CActiveRecord
{
    
        const STATUS_DRAFT=1;
        
        const STATUS_PUBLISHED=2;
        
        const STATUS_ARCHIVED=3;
        /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Post the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

        public function getUrl()
        {
            return Yii::app()->createUrl('post/view',array('id'=>$this->id,'title'=>$this->title,));
        }

        
        
        protected function beforeSave() {
            if(parent::beforeSave())
            {
                if($this->isNewRecord)
                {
                    $this->create_time = $this->update_time=time();
                    $this->author_id = Yii::app()->user->id;
                }
                else 
                {
                    $this->update_time = time();
                }
                return true;
            }
            else 
            {
                 return false;
            }
        }
        
        private $_oldTags;
        protected function afterSave() {
            parent::afterSave();
            Tag::model()->updateFrequency($this->_oldTags, $this->tags);
        }
        
        
        protected function afterFind() {
            parent::afterFind();
            $this->_oldTags = $this->tags;
        }
        /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{post}}';
	}

        
        public function normalizeTags($attribute,$params)
        {
            $this->tags = Tag::array2string(array_unique(Tag::string2array($this->tags)));
        }


        /**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, begin_time, end_time, content, status', 'required'),
			array('begin_time, end_time, status, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('title, location', 'length', 'max'=>128),
                        array('status', 'in', 'range'=>array(1,2,3)),
			array('category, cost', 'length', 'max'=>64),
                        array('tags','match', 'pattern'=>'/^[\w\s,]+$','message'=>'Tags can only contain word characters'),
			array('tags', 'normalizeTags'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, title, begin_time, end_time, location, category, content, tags, url, status, cost, create_time, update_time, author_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
                        'author' => array(self::BELONGS_TO,'User','author_id'),
			'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
                            'condition'=>'comments.status='.Comment::STATUS_APPROVED,
                            'order'=>'comments.create_time DESC'),
			'commentCount' => array(self::STAT, 'Comment', 'post_id',
                            'condition'=>'status='.Comment::STATUS_APPROVED),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Title',
			'begin_time' => 'Begin Time',
			'end_time' => 'End Time',
			'location' => 'Location',
			'category' => 'Category',
			'content' => 'Content',
			'tags' => 'Tags',
			'url' => 'Url',
			'status' => 'Status',
			'cost' => 'Cost',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'author_id' => 'Author',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('begin_time',$this->begin_time);
		$criteria->compare('end_time',$this->end_time);
		$criteria->compare('location',$this->location,true);
		$criteria->compare('category',$this->category,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('tags',$this->tags,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('cost',$this->cost,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('author_id',$this->author_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}