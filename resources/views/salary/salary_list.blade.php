@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-5">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="d-flex justify-content-between">
             <h2>Salary List</h2>
			  <div>
                  <a href="{{route('salary.index')}}" class="btn btn-info btn-sm"> Back </a>
				  
		      </div>
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
                      <th>User mobile</th>
                      <th>Amount</th>
                      <th>Salary type</th>
                      <th>Date time</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach($salary_list as $key=>$item)
                   <tr>
                      <td>{{$key + 1}}</td>
                      <td>{{$item->mobile}}</td>
                      <td>{{$item->amount}}</td>
					   @if($item->description == 1)
                      <td>Daily Salary</td>
					   @elseif($item->description == 2)
					   <td>Weakly Salary</td>
					   @elseif($item->description == 3)
					   <td>Monthly Salary</td>
					   @else
					   <td></td>
					   @endif
                      <td>{{$item->created_at}}</td>
                
                  
                   </tr>
                  @endforeach
                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>
</div> 



<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
  })
</script>
 @endsection