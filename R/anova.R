setwd("~/Desktop/sample_code/R")
df <- read.csv("./data/anova.csv", header = TRUE)
Treatment <- df[,"Treatment"]
Y <- df[,"Y"]
lm1 <- lm(Y~factor(Treatment))
plot(lm1)
anova(lm1)
summary(lm1)
