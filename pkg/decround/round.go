package decround

import (
	"github.com/shopspring/decimal"
	"math/big"
)

var (
	oneInt = big.NewInt(1)
)

// Func represents decimal rounding function type
type Func func(d decimal.Decimal, places int32) decimal.Decimal

// HalfDown rounds the decimal to places decimal places.
// If places < 0, it will round the integer part to the nearest 10^(-places).
// Half parts are rounded down.
//
// Example:
//
// 	   HalfDown(decimal.NewFromFloat(5.45), 1).String() // output: "5.4"
// 	   HalfDown(decimal.NewFromFloat(545), -1).String() // output: "540"
//
func HalfDown(d decimal.Decimal, places int32) decimal.Decimal {
	round := d.Round(places)
	remainder := d.Sub(round).Abs()

	half := decimal.New(5, -places-1)
	exp := round.Exponent()
	value := round.Coefficient()
	if remainder.Equals(half) {
		if value.Sign() < 0 {
			value.Add(value, oneInt)
		} else {
			value.Sub(value, oneInt)
		}
		round = decimal.NewFromBigInt(value, exp)
	}
	return round
}

// HalfDown rounds the decimal to places decimal places.
// If places < 0, it will round the integer part to the nearest 10^(-places).
// Half parts are rounded up.
//
// Example:
//
// 	   HalfDown(decimal.NewFromFloat(5.45), 1).String() // output: "5.4"
// 	   HalfDown(decimal.NewFromFloat(545), -1).String() // output: "540"
//
func HalfUp(d decimal.Decimal, places int32) decimal.Decimal {
	return d.Round(places)
}

// Truncate truncates off digits from the number, without rounding.
//
// NOTE: precision is the last digit that will not be truncated (must be >= 0).
//
// Example:
//
//     decimal.NewFromString("123.456").Truncate(2).String() // "123.45"
//
func Truncate(d decimal.Decimal, places int32) decimal.Decimal {
	return d.Truncate(places)
}

// HalfEven rounds the decimal to places decimal places.
// If the final digit to round is equidistant from the nearest two integers the
// rounded value is taken as the even number
//
// If places < 0, it will round the integer part to the nearest 10^(-places).
//
// Examples:
//
// 	   NewFromFloat(5.45).Round(1).String() // output: "5.4"
// 	   NewFromFloat(545).Round(-1).String() // output: "540"
// 	   NewFromFloat(5.46).Round(1).String() // output: "5.5"
// 	   NewFromFloat(546).Round(-1).String() // output: "550"
// 	   NewFromFloat(5.55).Round(1).String() // output: "5.6"
// 	   NewFromFloat(555).Round(-1).String() // output: "560"
//
func HalfEven(d decimal.Decimal, places int32) decimal.Decimal {
	return d.RoundBank(places)
}
