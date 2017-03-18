#include <stdio.h>
#include <stdbool.h>

bool sumofTwoEqualsX(int *arr, int x, int except, int last){
  int first = 0;
  while(first<last){
    if(first!=except && last!=except){
      printf("\t{%d, %d} = %d\n", arr[first], arr[last], (arr[first]+arr[last]) );
      if((arr[first]+arr[last])==x){
        return true;
      }else if(arr[first]+arr[last]<x){
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

bool sumOfAny3NosEqualsZero(int *arr, int len){
  int i = 0;
  for(i=0; i<len; i++){
    printf("%d ", arr[i]);
    if(sumofTwoEqualsX( arr, -(arr[i]), i, len-1)==true){
      return true;
    }
  }
  return false;
}

int main(){
  int arr[] = {-5,-4,-3,4,5,6};
  int len = sizeof(arr)/sizeof(int);
  if(sumOfAny3NosEqualsZero(arr, len)==true){
    printf("Yes\n");
  }else{
    printf("No\n");
  }
  return 0;
}
