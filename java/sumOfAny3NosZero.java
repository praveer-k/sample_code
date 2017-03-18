public class sumOfAny3NosZero{
  public static void main(String []args){
    int[] arr = {-5,-4,-3,-2,2,3,4};
    if(sumOfAny3NosEqualsZero(arr)==true){
      System.out.println("yes");
    }else{
      System.out.println("yes");
    }
  }
  private static boolean sumOfAny3NosEqualsZero(int[] arr){
    for(int i=0; i<arr.length; i++){
      if(sumOfTwoEqualsX(arr, i, -arr[i])==true){
        return true;
      }
    }
    return false;
  }
  private static boolean sumOfTwoEqualsX(int[] arr, int except, int comp){
    int first = 0;
    int last = arr.length-1;
    while(first<last){
      if(first!=except && last!=except){
        System.out.format("%d\t{%d, %d} = %d\n", arr[except], arr[first], arr[last], (arr[first]+arr[last]) );
        if(arr[first]+arr[last]==comp){
          return true;
        }else if(arr[first]+arr[last]<comp){
          first++;
        }else{
          last--;
        }
      }else if(first==except){
        first++;
      }else{
        last--;
      }
    }
    return false;
  }
}
