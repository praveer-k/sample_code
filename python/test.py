count = 0
for i in range(1, 1000):
    if i%3==0 or i%5==0:
        print i, i%3, i%5
        count+=i
print count
