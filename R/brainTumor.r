fi = "fdg.dyn"
fa = "Attn"
faq = "FDG.acqtimes90"

tx = matrix(scan(faq,skip=2), ncol=2, byrow=T)
x = readBin(fa, n=128*128*35, numeric(), endian="big", size = 8)
length(x)/(128*128*35)
x = matrix(x,ncol=35)
dim(x)

par(mfrow=c(3,3))
for(j in 1:9){
  k=j*3
  u = matrix(x[,k],ncol=128)
  gs=grey(c(0:128)/128)
  v=u
  for(i in 1:128){
    v[i,] = rev(u[i,])
  }
  image(v,col=gs)
}
