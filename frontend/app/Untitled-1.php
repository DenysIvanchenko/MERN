<?php if(count($_GET) > 0){?>
<script type="text/javascript">
	$(document).on('change keyup keydown', '.calculate_total',function(){
				var _total_marks = jQuery(this).closest('tr').find('.calculate_total').map((_,el) => parseFloat(el.value) || 0).get().reduce(function(a, b) { return a + b; });
				let _tot_columns = parseFloat('{{ $get_exam_sets->count() }}');
				let _avg_tot = (_total_marks / _tot_columns);
				jQuery(this).closest('tr').find("input.result_total").val(_total_marks);
				jQuery(this).closest('tr').find("input.result_avg").val(_avg_tot);
				jQuery(this).closest('tr').find("span.result").text(_total_marks);
	});
</script>
<?php }?>