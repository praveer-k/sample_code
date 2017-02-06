def least_disruptive(arr, subarr):
    dis = []
    for i in range(0, len(arr)-len(subarr)):
        _sum = 0
        for j in range(0, len(subarr)):
            _sum+= abs(arr[i] - subarr[j])
        dis+=[_sum]
    _min = min(dis)
    return dis.index(_min)

arr = [2,2,3,4,5]
subarr = [3,5,3]
l = least_disruptive(arr, subarr)
print l
