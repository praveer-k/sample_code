#include <stdio.h>
/*
 * Swap elements of the array using reference.
 */
void swap(int *a, int *b){
  int temp = *a;
  *a = *b;
  *b = temp;
}
/*
 * Arrange elements of the array using Bubble Sort.
 */
void bubbleSort(int *arr, int len){
  int i,j;
  for(i=0;i<len;i++){
    for(j=0;j<len-i-1;j++){
      if(arr[j]>arr[j+1]){
        swap(&arr[j], &arr[j+1]);
      }
    }
  }
}
/*
 * Print elements of an array.
 */
void print(int *arr, int len){
  int i = 0;
  printf("%d - {",len);
  for(i=0; i<len; i++){
    if(i==len-1){
      printf("%d", *(arr+i) );
    }else{
      printf("%d, ", *(arr+i) );
    }
  }
  printf("}\n");
}
/*
 * Main()
 */
int main(){
  int arr[] = {-5,2,3,4,5,6,1,3,4,0,-2};
  int len = sizeof(arr)/sizeof(int);
  printf("Array Before Sorting... ");
  print(arr, len);
  bubbleSort(arr, len);
  printf("Array After Sorting... ");
  print(arr, len);
  return 0;
}
