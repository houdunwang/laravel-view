<div class="form-group row">
    <label for="{{$field['name']}}" class="col-3 col-lg-2 col-form-label text-right">{{$field['title']}}</label>
    <div class="col-9 col-lg-10">
        <hd-datetimepicker name="{{$field['name']}}" id="{{$field['name']}}" value="{{$field['value']}}"></hd-datetimepicker>
    </div>
</div>
