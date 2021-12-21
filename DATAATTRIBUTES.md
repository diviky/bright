## Data attributes

### data-delete

This data attribute is used to delete the record from the database table. On click of this element it will take the value of the href as URL and send as get request.

#### Example code

```php

<a data-delete href="{{ url('vmn/flow/delete/1') }}" data-toggle="tooltip" title="delete flow" 
	class="btn btn-secondary btn-sm">
    <i class="fe fe-x-square"></i>
</a>
```


### data-action="delete"

This data attribute is used to delete the record from the database table. On click of this element it will take the value of the href as URL and send as get request.

#### Example code

```php

<a data-action="delete" href="{{ url('vmn/flow/delete/1') }}" data-toggle="tooltip" title="delete flow" 
	class="btn btn-secondary btn-sm">
    <i class="fe fe-x-square"></i>
</a>
```

### data-href

This data attribute is used to hit specific URL. On click of this element it will take the value of the data attribute data-href as URL and send as get request.

#### Example code

```php

<a data-href="{{ url('vmn/flow/copy/1') }}" data-render="true" data-toggle="tooltip" title="Clone this flow" 
	class="btn btn-secondary btn-sm">
<i class="fe fe-copy"></i>
</a>
```

### data-dateranges-report

On Click of this attribute it will give customizable calender for choosing date ranges.

#### Example code

```php

<input type="text" name="datetime[created_at]" data-auto-submit data-dateranges-report class="form-control">
```
### data-auto-submit

This attribute will be  used in filrering purpose, once dataranges are choosen it will get all filter details and subimt the form with POST method.

#### Example code

```php

<input type="text" name="datetime[created_at]" data-auto-submit data-dateranges-report class="form-control">
```

### data-order

On click of this element it will rearrange the data based on value(database table column) provided to data attribute data-order(desc vs asc)

#### Example code

```php

<th width="140" data-order="updated_at">updated_at</th>
```
### data-add

On click of this data attribute it will clone the table footer first tr element and append to table body.

### data-remove

On click of this data attribute it will remove the current tr element.

#### Example code

```php

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th width="10">
                <button class="btn btn-sm btn-secondary" data-add>
                    <i class="fe fe-plus"></i>
                </button>
            </th>
        </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot class="hide">
        <tr>
            <td>
                <input type="text" class="form-control" name="name[]" value="" />
            </td>
            <td>
                <input type="text" class="form-control" name="email[]" value="" />
            </td>
            <td>
                <button class="btn btn-secondary btn-sm" data-remove>
                    <i class="fe fe-minus"></i>
                </button>
            </td>
        </tr>
    </tfoot>
</table>
```

### auto-submit

On changing of the dropdown it will take all the form details and submit form. Basically This attribute will be used in listing filters if any dropdown is there.

#### Example code

```php

<form role="ksubmit" method="post" action="{{ url('/sms/upload') }}" data-clear="true" data-progress=".progress-bar" data-onsuccess="uploaded" enctype="multipart/form-data">
    <input type="file" name="file" auto-submit id="uploadfile" size="1" style="display:none" />
</form>
```

### auto-keyup-submit

This attribute will use in data listing filters. On keyup event it will take the value of the current field with remaining form details and submit the form.

#### Example code

```php

<input type="text" class="form-control w-10" name="lfilter[name]" auto-keyup-submit
    placeholder="Search Name">
```
### data-check-all

This attriute will be used for bulk deleting records from the database table. on click of this checkbox, automatically it will choose all the records and will display the count of records and one delete button, on click of delete button all the Checked records will delete (we can uncheck unwanted record). 

#### Example code

```php

<th class="w-1">
    <label class="custom-control custom-checkbox-only custom-checkbox custom-control-inline">
        <input type="checkbox" class="custom-control-input" data-check-all>
        	<span class="custom-control-label"></span>
    </label>
</th>
```

### data-off-checked

On click of this data-check-all data-attribute, data-off-checked element style will be display none.

### data-on-checked

on click of this data-check-all data-attribute, data-on-checked element style will be display.

#### Example code

```php

<table class="table card-tables table-vcenter text-nowrap">
    <thead data-off-checked>
        <tr>
            <th class="w-1">
                <label class="custom-control custom-checkbox-only custom-checkbox custom-control-inline">
                    <input type="checkbox" class="custom-control-input" data-check-all>
                    <span class="custom-control-label"></span>
                </label>
            </th>
            <th>First Name</th>
            <th>Last Name</th>
            <th></th>
        </tr>
    </thead>
    <thead data-on-checked class="hide">
        <tr>
            <th class="w-1">
                <label class="custom-control custom-checkbox-only custom-checkbox custom-control-inline">
                    <input type="checkbox" class="custom-control-input" checked data-check-alls>
                    <span class="custom-control-label"></span>
                </label>
            </th>
            <th colspan="8">
                <span data-checked-count></span> selected &nbsp;
                <i class="fe fe-arrow-right"></i> &nbsp;
                <a href="#" data-task="delete">Delete</a>
            </th>
        </tr>
    </thead>
    <tbody ajax-content data-checked>
        <tr>
            <td class="text-center">
                <label class="custom-control custom-checkbox-only custom-checkbox custom-control-inline">
                    <input type="checkbox" class="custom-control-input" name="id[]" value="1">
                    <span class="custom-control-label"></span>
                </label>
            </td>
            <td>Satish</td>
            <td>Naga</td>
            <td class="text-bold">8074XXXXXX</td>
        </tr>
    </tbody>
</table>
```

### data-checked-count

On click of this data-check-all data attribute, data-checked-count element will display the count of the choosen records.

#### Example code

```php

<th colspan="3">
    <span data-checked-count></span> selected &nbsp;
    <i class="fe fe-arrow-right"></i> &nbsp;
    <a href="#" data-task="delete">Delete</a>
</th>
```
### data-post

On click of this data attribute, It will take the value of the data-post as URL and hit as a POST method.

#### Example code

```php

<div class="text-right text-bold">
    <a href="#" class="text-red" data-post="{{ url('record/delete/1') }}">Delete this record</a>
</div>
```

### data-post

On click of this data attribute, It will take the value of the href as URL and hit as a GET method (You can define your own method like data-method="POST" or data-method="GET" ).

#### Example code

```php

<a class="dropdown-item cursor" data-callback="addFav" href="{{ url('record/item/1') }}" data-ajax>Mobtexxting</a>
```
### data-change-href

On change  of dropdown, choosen value will be appended to the URL that is value ofdata-change-href and hit as a GET method (You can define your own method like data-method="POST" or data-method="GET" ) so you can get dynamic data based on choosen value from dropdown.

#### Example code

```php
<select name="template_id" class="form-control" data-select data-change-href="{{ url('record/item') }}" data-callback="addFav">
    <option value="">Choose</option>
    <option value="1">Active</option>
    <option value="0">In Active</option>
</select>
```

### ajax-export

On click  of this data attribute, It will get the form details and submit the form. Basically we are using for exports.

```php

<a href="{{ url('record/export') }}" data-toggle="tooltip" title="Download records"
    class="btn btn-primary" ajax-export>
    <i class="fe fe-download"></i>
</a>
```