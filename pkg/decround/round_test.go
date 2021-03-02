package decround

import (
	"github.com/shopspring/decimal"

	"testing"
)

func TestDecimal_HalfDownRound(t *testing.T) {
	type testData struct {
		input    string
		places   int32
		expected string
	}
	tests := []testData{
		{"1.454", 0, "1"},
		{"1.454", 1, "1.5"},
		{"1.454", 2, "1.45"},
		{"1.454", 3, "1.454"},
		{"1.454", 4, "1.454"},
		{"1.454", 5, "1.454"},
		{"1.554", 0, "2"},
		{"1.554", 1, "1.6"},
		{"1.554", 2, "1.55"},
		{"0.554", 0, "1"},
		{"0.454", 0, "0"},
		{"0.454", 5, "0.454"},
		{"0", 0, "0"},
		{"0", 1, "0"},
		{"0", 2, "0"},
		{"0", -1, "0"},
		{"5", 2, "5"},
		{"5", 1, "5"},
		{"5", 0, "5"},
		{"500", 2, "500"},
		{"545", -2, "500"},
		{"545", -3, "1000"},
		{"545", -4, "0"},
		{"499", -3, "0"},
		{"499", -4, "0"},
		{"1.45", 1, "1.4"},
		{"1.55", 1, "1.5"},
		{"1.65", 1, "1.6"},
		{"545", -1, "540"},
		{"565", -1, "560"},
		{"555", -1, "550"},
	}

	// add negative number tests
	for _, test := range tests {
		expected := test.expected
		if expected != "0" {
			expected = "-" + expected
		}
		tests = append(tests, testData{"-" + test.input, test.places, expected})
	}

	for _, test := range tests {
		d, err := decimal.NewFromString(test.input)
		if err != nil {
			panic(err)
		}
		// test Round
		expected, err := decimal.NewFromString(test.expected)
		if err != nil {
			panic(err)
		}
		got := HalfDown(d, test.places)
		if !got.Equal(expected) {
			t.Errorf("Half Down Rounding %s to %d places, got %s, expected %s",
				d, test.places, got, expected)
		}
	}
}

func TestDecimal_HalfUpRound(t *testing.T) {
	type testData struct {
		input    string
		places   int32
		expected string
	}
	tests := []testData{
		{"1.454", 0, "1"},
		{"1.454", 1, "1.5"},
		{"1.454", 2, "1.45"},
		{"1.454", 3, "1.454"},
		{"1.454", 4, "1.454"},
		{"1.454", 5, "1.454"},
		{"1.554", 0, "2"},
		{"1.554", 1, "1.6"},
		{"1.554", 2, "1.55"},
		{"0.554", 0, "1"},
		{"0.454", 0, "0"},
		{"0.454", 5, "0.454"},
		{"0", 0, "0"},
		{"0", 1, "0"},
		{"0", 2, "0"},
		{"0", -1, "0"},
		{"5", 2, "5"},
		{"5", 1, "5"},
		{"5", 0, "5"},
		{"500", 2, "500"},
		{"545", -2, "500"},
		{"545", -3, "1000"},
		{"545", -4, "0"},
		{"499", -3, "0"},
		{"499", -4, "0"},
		{"1.45", 1, "1.5"},
		{"1.55", 1, "1.6"},
		{"1.65", 1, "1.7"},
		{"545", -1, "550"},
		{"565", -1, "570"},
		{"555", -1, "560"},
	}

	// add negative number tests
	for _, test := range tests {
		expected := test.expected
		if expected != "0" {
			expected = "-" + expected
		}
		tests = append(tests, testData{"-" + test.input, test.places, expected})
	}

	for _, test := range tests {
		d, err := decimal.NewFromString(test.input)
		if err != nil {
			panic(err)
		}
		// test Round
		expected, err := decimal.NewFromString(test.expected)
		if err != nil {
			panic(err)
		}
		got := HalfUp(d, test.places)
		if !got.Equal(expected) {
			t.Errorf("Half Up Rounding %s to %d places, got %s, expected %s",
				d, test.places, got, expected)
		}
	}
}

func TestDecimal_TruncateRound(t *testing.T) {
	type testData struct {
		input    string
		places   int32
		expected string
	}
	tests := []testData{
		{"1.454", 0, "1"},
		{"1.454", 1, "1.4"},
		{"1.454", 2, "1.45"},
		{"1.454", 3, "1.454"},
		{"1.454", 4, "1.454"},
		{"1.454", 5, "1.454"},
		{"1.554", 0, "1"},
		{"1.554", 1, "1.5"},
		{"1.554", 2, "1.55"},
		{"0.554", 0, "0"},
		{"0.454", 0, "0"},
		{"0.454", 5, "0.454"},
		{"0", 0, "0"},
		{"0", 1, "0"},
		{"0", 2, "0"},
		{"0", -1, "0"},
		{"5", 2, "5"},
		{"5", 1, "5"},
		{"5", 0, "5"},
		{"500", 2, "500"},
		{"545", -2, "545"},
		{"545", -3, "545"},
		{"545", -4, "545"},
		{"499", -3, "499"},
		{"499", -4, "499"},
		{"1.45", 1, "1.4"},
		{"1.55", 1, "1.5"},
		{"1.65", 1, "1.6"},
		{"545", -1, "545"},
		{"565", -1, "565"},
		{"555", -1, "555"},
	}

	// add negative number tests
	for _, test := range tests {
		expected := test.expected
		if expected != "0" {
			expected = "-" + expected
		}
		tests = append(tests, testData{"-" + test.input, test.places, expected})
	}

	for _, test := range tests {
		d, err := decimal.NewFromString(test.input)
		if err != nil {
			panic(err)
		}
		// test Round
		expected, err := decimal.NewFromString(test.expected)
		if err != nil {
			panic(err)
		}
		got := Truncate(d, test.places)
		if !got.Equal(expected) {
			t.Errorf("Truncate Rounding %s to %d places, got %s, expected %s",
				d, test.places, got, expected)
		}
	}
}

func TestDecimal_HalfEvenRound(t *testing.T) {
	type testData struct {
		input    string
		places   int32
		expected string
	}
	tests := []testData{
		{"1.454", 0, "1"},
		{"1.454", 1, "1.5"},
		{"1.454", 2, "1.45"},
		{"1.454", 3, "1.454"},
		{"1.454", 4, "1.454"},
		{"1.454", 5, "1.454"},
		{"1.554", 0, "2"},
		{"1.554", 1, "1.6"},
		{"1.554", 2, "1.55"},
		{"0.554", 0, "1"},
		{"0.454", 0, "0"},
		{"0.454", 5, "0.454"},
		{"0", 0, "0"},
		{"0", 1, "0"},
		{"0", 2, "0"},
		{"0", -1, "0"},
		{"5", 2, "5"},
		{"5", 1, "5"},
		{"5", 0, "5"},
		{"500", 2, "500"},
		{"545", -2, "500"},
		{"545", -3, "1000"},
		{"545", -4, "0"},
		{"499", -3, "0"},
		{"499", -4, "0"},
		{"1.45", 1, "1.4"},
		{"1.55", 1, "1.6"},
		{"1.65", 1, "1.6"},
		{"545", -1, "540"},
		{"565", -1, "560"},
		{"555", -1, "560"},
	}

	// add negative number tests
	for _, test := range tests {
		expected := test.expected
		if expected != "0" {
			expected = "-" + expected
		}
		tests = append(tests, testData{"-" + test.input, test.places, expected})
	}

	for _, test := range tests {
		d, err := decimal.NewFromString(test.input)
		if err != nil {
			panic(err)
		}
		// test Round
		expected, err := decimal.NewFromString(test.expected)
		if err != nil {
			panic(err)
		}
		got := HalfEven(d, test.places)
		if !got.Equal(expected) {
			t.Errorf("Truncate Rounding %s to %d places, got %s, expected %s",
				d, test.places, got, expected)
		}
	}
}
