#include <iostream>
using namespace std;

bool sumofTwoEqualsX(int *arr, int skip, int num, int last){
  int first = 0;
  while(first<last){
    if(first!=skip && last!=skip){
      cout<<"\t{"<< arr[first]<<", "<<arr[last]<<"} = "<<(arr[first]+arr[last])<<"\n";
      if(arr[first]+arr[last]==num){
        return true;
      }else if(arr[first]+arr[last]<num){
        first++;
      }else{
        last--;
      }
    }else if(first==skip){
      first++;
    }else{
      last--;
    }
  }
  return false;
}

bool sumOfAny3NosEqualsZero(int *arr, int len){
  for(int i=0; i<len; i++){
    cout<<arr[i];
    if(sumofTwoEqualsX(arr, i, -arr[i], len-1)==true){
      return true;
    }
  }
  return false;
}

int main(){
  int arr[] = {-5,-4,-3,-2,-1,0,1,2,3,4,5,6};
  int len = sizeof(arr)/sizeof(int);
  if(sumOfAny3NosEqualsZero(arr, len)==true){
    cout<<"yes\n";
  }else{
    cout<<"no\n";
  }
  return 0;
}
